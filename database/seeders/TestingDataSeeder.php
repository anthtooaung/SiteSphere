<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Posts;
use App\Models\Comments;

class TestingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a primary user for testing logged-in interactions
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'is_verified' => 1,
            ]
        );

        // 2. Create additional users
        $additionalUsers = \App\Models\User::factory()->count(5)->create();

        // 3. Create categories and tags
        $categories = \App\Models\Categories::factory()->count(3)->create();
        $tags = \App\Models\Tags::factory()->count(5)->create();

        // 4. Create posts
        $posts = \App\Models\Posts::factory()->count(10)->create();

        $posts->each(function ($post) use ($user, $additionalUsers, $categories, $tags) {
            // Associate post with primary user via UserPosts model
            \App\Models\UserPosts::factory()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);

            // Assign random tags
            $post->tags()->attach($tags->random(2)->pluck('id')->toArray());

            // 5. Create comments (from primary and additional users)
            $allUsers = $additionalUsers->push($user);
            $comments = \App\Models\Comments::factory()->count(3)->create([
                'post_id' => $post->id,
                'user_id' => $allUsers->random()->id,
            ]);

            // 6. Create rating
            \App\Models\Ratings::factory()->create([
                'post_id' => $post->id,
                'user_id' => $additionalUsers->random()->id,
            ]);
            
            // 7. Create reactions to the comments created above
            foreach ($comments as $comment) {
                \App\Models\CommentReactions::factory()->count(1)->create([
                    'comment_id' => $comment->id,
                    'user_id' => $additionalUsers->random()->id,
                ]);
            }

            // 8. Create a report for this post
            \App\Models\Reports::factory()->create([
                'user_id' => $additionalUsers->random()->id,
                'target_name' => 'posts',
                'target_id' => $post->id,
            ]);
        });
    }
}
