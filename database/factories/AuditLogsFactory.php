<?php

namespace Database\Factories;

use App\Models\AuditLogs;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLogs>
 */
class AuditLogsFactory extends Factory
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
            'action' => $this->faker->word(),
            'category' => $this->faker->randomElement(['moderation', 'success', 'announcement', 'system']),
            'target_type' => 'App\Models\Post',
            'target_id' => $this->faker->randomNumber(),
            'reason' => $this->faker->sentence(),
        ];
    }
}
