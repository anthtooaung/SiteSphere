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
        Schema::table('otp_verifications', function (Blueprint $table) {
            if (! Schema::hasColumn('otp_verifications', 'email')) {
                $table->string('email')->nullable()->after('user_id')->index();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `otp_verifications` MODIFY `user_id` BIGINT UNSIGNED NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('otp_verifications')->whereNull('user_id')->delete();

        Schema::table('otp_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('otp_verifications', 'email')) {
                $table->dropIndex(['email']);
                $table->dropColumn('email');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `otp_verifications` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');
        }
    }
};
