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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['verified', 'unsecure', 'banned'])->default('verified')->after('role');
            $table->foreignId('banned_by')->nullable()->after('status');
            $table->timestamp('banned_at')->nullable()->after('banned_by');
            $table->string('ban_reason')->nullable()->after('banned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'banned_by', 'banned_at', 'ban_reason']);
        });
    }
};
