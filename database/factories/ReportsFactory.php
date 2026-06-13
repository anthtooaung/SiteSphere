<?php

namespace Database\Factories;

use App\Models\Reports;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reports>
 */
class ReportsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $targetType = $this->faker->randomElement(['users', 'posts', 'comments']);
        
        return [
            'user_id' => \App\Models\User::factory(),
            'target_name' => $targetType,
            'target_id' => match ($targetType) {
                'users' => \App\Models\User::factory(),
                'posts' => \App\Models\Posts::factory(),
                'comments' => \App\Models\Comments::factory(),
            },
            'reason' => $this->faker->paragraph(),
            'admin_read' => $this->faker->boolean(),
        ];
    }
}
