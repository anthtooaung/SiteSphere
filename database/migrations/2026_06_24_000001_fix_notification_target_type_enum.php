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
        Schema::table('notificatioins', function (Blueprint $table) {
            $table->enum('target_type', ['posts', 'comments', 'users'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notificatioins', function (Blueprint $table) {
            $table->enum('target_type', ['posts', 'comments'])->change();
        });
    }
};
