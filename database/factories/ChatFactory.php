<?php

namespace Database\Factories;

use App\Enums\ChatType;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(ChatType::cases())->value,
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'avatar' => $this->faker->filePath(),
            'last_message_id' => $this->faker->boolean() ? Message::factory() : null,
        ];
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChatType::PRIVATE->value,
        ]);
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChatType::GROUP->value,
        ]);
    }
}
