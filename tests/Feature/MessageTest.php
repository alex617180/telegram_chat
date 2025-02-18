<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use App\Services\TelegramService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class MessageTest extends ApiTestCase
{
    #[Test]
    public function it_should_return_paginated_messages()
    {
        Message::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/messages', $this->authHeader());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'telegram_chat_id',
                        'telegram_message_id',
                        'telegram_first_name',
                        'datetime',
                        'text',
                        'is_from_guest',
                        'reply_to_message_id',
                        'created_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                    'links',
                ],
            ]);
    }

    #[Test]
    public function it_should_return_paginated_messages_if_not_authenticated()
    {
        $response = $this->getJson('/api/v1/messages');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthorized']);;
    }

    
    #[Test]
    public function it_should_reply_to_message()
    {
        $message = Message::factory()->create();

        $telegramServiceMock = Mockery::mock(TelegramService::class);
        $this->app->instance(TelegramService::class, $telegramServiceMock);

        // Ожидаем вызов метода sendMessage и возвращаем фейковый ответ
        $telegramServiceMock->shouldReceive('sendMessage')
            ->once()
            ->withArgs([$message->telegram_chat_id, 'Ответ на сообщение', $message->telegram_message_id])
            ->andReturn([
                'result' => [
                    'message_id' => 12345,
                    'date' => now()->timestamp,
                    'chat' => ['first_name' => 'TestUser'],
                ]
            ]);

        $response = $this->postJson("/api/v1/messages/{$message->id}/reply", [
            'text' => 'Ответ на сообщение',
            'with_reply' => true,
        ], $this->authHeader());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message' => [
                    'telegram_message_id',
                    'chat_id',
                    'first_name',
                    'text',
                    'datetime',
                    ]
            ]);
    }
    
    #[Test]
    public function it_should_return_unauthorized_if_no_token()
    {
        $message = Message::factory()->create();
    
        $response = $this->postJson("/api/v1/messages/{$message->id}/reply", [
            'text' => 'Ответ без токена',
        ]);
    
        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Unauthorized',
            ]);
    }

    #[Test]
    public function it_should_return_not_found_if_message_does_not_exist()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->postJson("/api/v1/messages/99999/reply", [
            'text' => 'Ответ на несуществующее сообщение',
        ], $this->authHeader());

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
                'message' => 'Сообщение не найдено',
            ]);
    }

    #[Test]
    public function it_should_return_validation_error_if_text_is_missing()
    {
        $message = Message::factory()->create();

        $response = $this->postJson("/api/v1/messages/{$message->id}/reply", [], $this->authHeader());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text']);
    }

    #[Test]
    public function it_should_return_error_if_telegram_fails()
    {
        $message = Message::factory()->create();

        $telegramServiceMock = Mockery::mock(TelegramService::class);
        $this->app->instance(TelegramService::class, $telegramServiceMock);

        // Ожидаем вызов метода sendMessage и возвращаем фейковый ответ с ошибкой
        $telegramServiceMock->shouldReceive('sendMessage')
            ->once()
            ->andReturn(['error' => 'Telegram API Error']);

        $response = $this->postJson("/api/v1/messages/{$message->id}/reply", [
            'text' => 'Ошибка отправки',
            'with_reply' => true,
        ], $this->authHeader());

        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'Ошибка отправки сообщения',
            ]);
    }
}
