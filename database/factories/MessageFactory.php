<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'telegram_chat_id'    => $this->faker->numberBetween(100000, 999999),
            'telegram_message_id' => $this->faker->numberBetween(1, 10000),
            'telegram_first_name' => $this->faker->firstName,
            'user_id'             => $this->faker->optional()->randomElement(User::pluck('id')->toArray()),
            'text'                => $this->faker->sentence,
            'datetime'            => Carbon::now()->subMinutes(rand(1, 10000)),
            'is_from_guest'       => $this->faker->boolean,
            'reply_to_message_id' => function () {
                return rand(0, 1) ? Message::inRandomOrder()->value('id') : null;
            },
        ];
    }
}
