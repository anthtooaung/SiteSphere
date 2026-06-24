<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add status column to users for unsecure/secure/banned states
        Schema::table('users', function (Blueprint $table): void {
            $table->string('status')->default('verified')->after('is_verified');
            $table->foreignId('banned_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('banned_at')->nullable()->after('banned_by');
            $table->string('ban_reason')->nullable()->after('banned_at');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->boolean('is_unsecure')->default(false);
        });

        Schema::table('user_posts', function (Blueprint $table): void {
            $table->boolean('is_unsecure')->default(false);
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->boolean('is_unsecure')->default(false);
        });

        // Convert existing soft-deleted posts to unsecure
        DB::table('posts')
            ->whereNotNull('deleted_at')
            ->update(['is_unsecure' => true]);

        // Clear deleted_at so they're visible again as 'unsecure' posts
        DB::table('posts')
            ->whereNotNull('deleted_at')
            ->update(['deleted_at' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['banned_by']);
            $table->dropColumn(['status', 'banned_by', 'banned_at', 'ban_reason']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn('is_unsecure');
        });

        Schema::table('user_posts', function (Blueprint $table): void {
            $table->dropColumn('is_unsecure');
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropColumn('is_unsecure');
        });
    }
};
