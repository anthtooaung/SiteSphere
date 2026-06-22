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
        Schema::table('reports', function (Blueprint $table): void {
            $table->string('status')->default('new')->after('reason');
            $table->string('category')->nullable()->after('status');
            $table->timestamp('resolved_at')->nullable()->after('admin_read');
            $table->timestamp('closed_at')->nullable()->after('resolved_at');
        });

        // Migrate existing data
        DB::table('reports')->where('admin_read', false)->update(['status' => 'new']);
        DB::table('reports')->where('admin_read', true)->update(['status' => 'resolved_no_action', 'resolved_at' => now()]);

        Schema::table('reports', function (Blueprint $table): void {
            $table->dropColumn('admin_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->boolean('admin_read')->default(false)->after('reason');
        });

        // Reverse data migration
        DB::table('reports')->where('status', 'new')->update(['admin_read' => false]);
        DB::table('reports')->where('status', '!=', 'new')->update(['admin_read' => true]);

        Schema::table('reports', function (Blueprint $table): void {
            $table->dropColumn(['status', 'category', 'resolved_at', 'closed_at']);
        });
    }
};
