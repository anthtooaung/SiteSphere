<?php

namespace Database\Factories;

use App\Models\CommentReactions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentReactions>
 */
class CommentReactionsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'helpful' => $this->faker->boolean(),
        ];
    }
}
