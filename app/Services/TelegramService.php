<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Message;

class TelegramService
{
    protected $client;
    protected $token;
    protected $timeout;
    protected $baseUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->timeout = config('services.telegram.poll_timeout');
        $this->baseUrl = "https://api.telegram.org/bot{$this->token}/";
        $this->client = new Client();
    }

    /**
     * Метод для получения обновлений через long polling.
     *
     * @param int $offset
     * @param int $timeout Время ожидания в секундах.
     * @return array
     */
    public function getUpdates($offset = 0, $timeout = 30)
    {
        $response = $this->client->get($this->baseUrl . 'getUpdates', [
            'query' => [
                'offset' => $offset,
                'timeout' => $timeout,
            ],
            'timeout' => $timeout + 5, // немного больше времени ожидания
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['result'] ?? [];
    }

    /**
     * Обработка входящего обновления.
     *
     * @param array $update
     * @return void
     */
    public function handleUpdate(array $update)
    {
        if (isset($update['message'])) {
            $messageData = $update['message'];

            Message::create([
                'telegram_chat_id' => $messageData['chat']['id'],
                'telegram_message_id' => $messageData['message_id'],
                'telegram_first_name' => $messageData['chat']['first_name'] ?? '',
                'text' => $messageData['text'] ?? '',
                'datetime' => date('Y-m-d H:i:s', $messageData['date']),
                'is_from_guest' => true,
                'data' => $messageData,
            ]);

        }
    }

    /**
     * Отправка сообщения через Telegram API.
     *
     * @param int         $chatId           Идентификатор чата, куда отправляется сообщение.
     * @param string      $text             Текст сообщения.
     * @param int|null    $replyToMessageId (Опционально) Идентификатор сообщения, на которое производится ответ.
     * @return array
     */
    public function sendMessage(int $chatId, string $text, int $replyToMessageId = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text'    => $text,
        ];

        // Если передан идентификатор исходного сообщения, добавляем параметр для ответа
        if ($replyToMessageId) {
            $params['reply_to_message_id'] = $replyToMessageId;
        }

        $response = $this->client->post($this->baseUrl . 'sendMessage', [
            'json' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }
}
