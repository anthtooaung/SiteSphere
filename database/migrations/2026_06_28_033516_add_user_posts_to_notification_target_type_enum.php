<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE notificatioins MODIFY COLUMN target_type ENUM('posts', 'comments', 'users', 'user_posts') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE notificatioins MODIFY COLUMN target_type ENUM('posts', 'comments', 'users') NOT NULL");
    }
};
