<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\CommentReactions;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\Reports;
use App\Models\Tags;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Database\Seeder;

class TestingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create a primary user for testing logged-in interactions
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'is_verified' => 1,
            ]
        );

        // 2. Create additional users
        $additionalUsers = User::factory()->count(5)->create();

        // 3. Create categories and tags
        $categories = Categories::factory()->count(3)->create();
        $tags = Tags::factory()->count(5)->create();

        // 4. Create posts
        $posts = Posts::factory()->count(10)->create();

        $posts->each(function ($post) use ($user, $additionalUsers, $tags) {
            // Associate post with primary user via UserPosts model
            UserPosts::factory()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);

            // Assign random tags
            $post->tags()->attach($tags->random(2)->pluck('id')->toArray());

            // 5. Create comments (from primary and additional users)
            $allUsers = $additionalUsers->push($user);
            $comments = Comments::factory()->count(3)->create([
                'post_id' => $post->id,
                'user_id' => $allUsers->random()->id,
            ]);

            // 6. Create rating
            Ratings::factory()->create([
                'post_id' => $post->id,
                'user_id' => $additionalUsers->random()->id,
            ]);

            // 7. Create reactions to the comments created above
            foreach ($comments as $comment) {
                CommentReactions::factory()->count(1)->create([
                    'comment_id' => $comment->id,
                    'user_id' => $additionalUsers->random()->id,
                ]);
            }

            // 8. Create a report for this post
            Reports::factory()->create([
                'user_id' => $additionalUsers->random()->id,
                'target_name' => 'posts',
                'target_id' => $post->id,
            ]);
        });
    }
}
