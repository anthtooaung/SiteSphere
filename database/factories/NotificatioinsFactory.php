<?php

namespace Database\Factories;

use App\Models\Notificatioins;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notificatioins>
 */
class NotificatioinsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'to_user_id' => User::factory(),
            'from_user_id' => User::factory(),
            'target_type' => fake()->randomElement(['posts', 'comments']),
            'target_id' => fake()->numberBetween(1, 1000),
            'message' => fake()->sentence(),
            'is_read' => false,
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_read' => true,
        ]);
    }
}
