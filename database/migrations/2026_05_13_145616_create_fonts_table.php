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
        Schema::create('fonts', function (Blueprint $table) {
            $table->id();
            $table->string('font_family');
            $table->timestamps();
        });

        Schema::create('user_current_fonts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('font_id')->constrained('fonts')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'font_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fonts');
        Schema::dropIfExists('user_current_fonts');
    }
};
