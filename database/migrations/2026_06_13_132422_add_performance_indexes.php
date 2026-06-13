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
        Schema::table('user_posts', function (Blueprint $table) {
            $table->index('user_hidden');
            $table->index(['user_id', 'user_hidden']);
        });

        Schema::table('notificatioins', function (Blueprint $table) {
            $table->index(['to_user_id', 'is_read']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index('slug');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_posts', function (Blueprint $table) {
            $table->dropIndex(['user_hidden']);
            $table->dropIndex(['user_id', 'user_hidden']);
        });

        Schema::table('notificatioins', function (Blueprint $table) {
            $table->dropIndex(['to_user_id', 'is_read']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
        });
    }
};
