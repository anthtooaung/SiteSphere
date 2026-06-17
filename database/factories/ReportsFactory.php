<?php

namespace Database\Factories;

use App\Models\Comments;
use App\Models\Posts;
use App\Models\Reports;
use App\Models\User;
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
            'user_id' => User::factory(),
            'target_name' => $targetType,
            'target_id' => match ($targetType) {
                'users' => User::factory(),
                'posts' => Posts::factory(),
                'comments' => Comments::factory(),
            },
            'reason' => $this->faker->paragraph(),
            'admin_read' => $this->faker->boolean(),
        ];
    }
}
