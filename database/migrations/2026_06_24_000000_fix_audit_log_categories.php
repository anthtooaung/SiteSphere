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
        // Fix 'system' categories to correct categories
        DB::table('audit_logs')->where('action', 'ban_post')->where('category', 'system')->update(['category' => 'moderation']);
        DB::table('audit_logs')->where('action', 'unban_post')->where('category', 'system')->update(['category' => 'resolved']);
        DB::table('audit_logs')->where('action', 'ban_audit')->where('category', 'system')->update(['category' => 'moderation']);
        DB::table('audit_logs')->where('action', 'unban_audit')->where('category', 'system')->update(['category' => 'resolved']);
        DB::table('audit_logs')->where('action', 'read_report')->where('category', 'system')->update(['category' => 'check']);
        DB::table('audit_logs')->where('action', 'unread_report')->where('category', 'system')->update(['category' => 'check']);
        DB::table('audit_logs')->where('action', 'delete_report')->where('category', 'system')->update(['category' => 'moderation']);
        DB::table('audit_logs')->where('action', 'update_tag_taxonomy')->where('category', 'system')->update(['category' => 'announcement']);

        // Fix unban/restore actions that were incorrectly set to 'moderation'
        DB::table('audit_logs')->where('action', 'unban_audit')->where('category', 'moderation')->update(['category' => 'resolved']);
        DB::table('audit_logs')->where('action', 'unban_comment')->where('category', 'moderation')->update(['category' => 'resolved']);
        DB::table('audit_logs')->where('action', 'restore_user')->where('category', 'moderation')->update(['category' => 'resolved']);
        DB::table('audit_logs')->where('action', 'unban_post')->where('category', 'moderation')->update(['category' => 'resolved']);

        // Fix 'success' to 'resolved' (old naming)
        DB::table('audit_logs')->where('category', 'success')->update(['category' => 'resolved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data fix migration, reversal is not recommended
        // as it would revert categories to incorrect states
    }
};
