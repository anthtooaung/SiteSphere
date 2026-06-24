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
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('report_count')->default(0)->after('url');
        });

        Schema::table('user_posts', function (Blueprint $table) {
            $table->integer('report_count')->default(0)->after('user_hidden');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->integer('report_count')->default(0)->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('report_count');
        });

        Schema::table('user_posts', function (Blueprint $table) {
            $table->dropColumn('report_count');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('report_count');
        });
    }
};
