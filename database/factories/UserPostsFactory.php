<?php

namespace Database\Factories;

use App\Models\Posts;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPosts>
 */
class UserPostsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Posts::factory(),
            'user_id' => User::factory(),
            'description' => fake()->paragraph(),
            'user_hidden' => false,
        ];
    }
}
