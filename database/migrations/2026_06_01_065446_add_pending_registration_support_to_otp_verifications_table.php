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
        Schema::table('otpVerifications', function (Blueprint $table) {
            if (! Schema::hasColumn('otpVerifications', 'email')) {
                $table->string('email')->nullable()->after('user_id')->index();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `otpVerifications` MODIFY `user_id` BIGINT UNSIGNED NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('otpVerifications')->whereNull('user_id')->delete();

        Schema::table('otpVerifications', function (Blueprint $table) {
            if (Schema::hasColumn('otpVerifications', 'email')) {
                $table->dropIndex(['email']);
                $table->dropColumn('email');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `otpVerifications` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
        }
    }
};
