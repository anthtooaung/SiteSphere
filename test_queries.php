<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use App\Http\Controllers\ProfileDetailController;
use App\Models\Posts;
use App\Models\Ratings;
use App\Models\User;
use App\Models\UserPosts;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$user = User::factory()->create();

for ($index = 1; $index <= 4; $index++) {
    $post = Posts::factory()->create([
        'title' => 'Profile Review '.$index,
    ]);

    UserPosts::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'description' => 'Profile review description '.$index,
    ]);

    Ratings::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'rating' => $index,
    ]);
}

$ratingQueries = [];
DB::listen(function ($query) use (&$ratingQueries) {
    if (str_contains($query->sql, 'from "ratings"') || str_contains($query->sql, 'from `ratings`')) {
        $ratingQueries[] = $query->sql;
    }
});

// simulate the request
$request = Request::create('/profile', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});
$controller = new ProfileDetailController;
$controller->__invoke($request);

print_r($ratingQueries);
