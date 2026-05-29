<?php

namespace Database\Factories;

use App\Models\Posts;
use App\Models\Ratings;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ratings>
 */
class RatingsFactory extends Factory
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
            'post_id' => Posts::factory(),
            'rating' => fake()->numberBetween(1, 5),
        ];
    }
}
