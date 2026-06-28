<?php

namespace Database\Factories;

use App\Models\Posts;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Posts>
 */
class PostsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $url = fake()->unique()->url();
        $host = parse_url($url, PHP_URL_HOST);
        $slug = $host ? preg_replace('/^www\./', '', $host) : Str::slug($url);

        return [
            'slug' => $slug,
            'url' => $url,
        ];
    }
}
