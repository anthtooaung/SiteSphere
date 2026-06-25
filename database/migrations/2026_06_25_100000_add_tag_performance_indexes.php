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
        Schema::table('categories', function (Blueprint $table) {
            $table->index('slug');
            $table->index('name');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->index('slug');
            $table->index('name');
        });

        Schema::table('category_tags', function (Blueprint $table) {
            $table->index(['category_id', 'tag_id']);
            $table->index('tag_id');
        });

        Schema::table('custom_tags', function (Blueprint $table) {
            $table->index(['user_id', 'tag_id']);
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropIndex(['name']);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropIndex(['name']);
        });

        Schema::table('category_tags', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'tag_id']);
            $table->dropIndex(['tag_id']);
        });

        Schema::table('custom_tags', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tag_id']);
            $table->dropIndex(['tag_id']);
        });
    }
};
