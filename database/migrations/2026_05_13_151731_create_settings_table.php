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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('menuBar_location',['top','right','bottom','left'])->default('right');
            $table->enum('noti_location',['top-start','top-end','bottom-end','bottom-start'])->default('top-end');
            $table->boolean('dark_mode')->default(false);
            $table->foreignId('theme_id')->constrained('themes')->onDelete('cascade');
            $table->foreignId('custom_theme_id')->nullable()->constrained('custom_themes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
