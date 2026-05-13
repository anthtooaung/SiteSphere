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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('tag_color');
            $table->timestamps();
        });

        Schema::create('custom_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->string('name');
            $table->string('color');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('custom_tags');
    }
};
