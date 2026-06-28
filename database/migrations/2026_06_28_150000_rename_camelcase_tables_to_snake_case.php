<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('socialAccounts') && ! Schema::hasTable('social_accounts')) {
            Schema::rename('socialAccounts', 'social_accounts');
        }

        if (Schema::hasTable('otpVerifications') && ! Schema::hasTable('otp_verifications')) {
            Schema::rename('otpVerifications', 'otp_verifications');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('social_accounts') && ! Schema::hasTable('socialAccounts')) {
            Schema::rename('social_accounts', 'socialAccounts');
        }

        if (Schema::hasTable('otp_verifications') && ! Schema::hasTable('otpVerifications')) {
            Schema::rename('otp_verifications', 'otpVerifications');
        }
    }
};
