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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->String('slug');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->string('email')->unique();
            $table->DateTime('user_dob')->nullable();
            $table->string('user_phone')->nullable();
            $table->longText('user_bio')->nullable();
            $table->string('user_image')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('is_verified')->default(true);
            $table->integer('report_count')->default(0);
            $table->string('password');
            $table->timestamps();
        });


        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
