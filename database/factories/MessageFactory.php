<?php

namespace Database\Factories;

use App\Enums\MessageType;
use App\Models\Chat;
use App\Models\Media;
use App\Models\Message;
use App\Models\User;
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
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'chat_id' => Chat::factory(),
            'message_type' => $this->faker->randomElement(MessageType::cases())->value,
            'content' => $this->faker->realText(),
            //'reply_id' => Message::factory(),
            'media_id' => Media::factory(),
            'is_forwarded' => $this->faker->boolean(),
        ];
    }
}
