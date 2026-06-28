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
        $title = fake()->sentence(4);

        return [
            'post_id' => Posts::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'description' => fake()->paragraph(),
            'user_hidden' => false,
        ];
    }
}
