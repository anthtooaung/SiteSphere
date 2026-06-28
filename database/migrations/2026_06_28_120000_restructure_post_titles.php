<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add title and slug to user_posts
        Schema::table('user_posts', function (Blueprint $table) {
            $table->string('title')->after('user_id');
            $table->string('slug')->after('title');
        });

        // Remove title from posts
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add title back to posts
        Schema::table('posts', function (Blueprint $table) {
            $table->string('title')->after('id');
        });

        // Remove title and slug from user_posts
        Schema::table('user_posts', function (Blueprint $table) {
            $table->dropColumn(['title', 'slug']);
        });
    }
};
