<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditLogsExtensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logs_table_has_category_column(): void
    {
        $this->assertTrue(Schema::hasColumn('audit_logs', 'category'));
    }

    public function test_audit_logs_model_returns_correct_icon_based_on_category(): void
    {
        $log = new \App\Models\AuditLogs(['category' => 'moderation']);
        $this->assertEquals('fa-hammer', $log->getIcon());

        $log = new \App\Models\AuditLogs(['category' => 'success']);
        $this->assertEquals('fa-check-circle', $log->getIcon());

        $log = new \App\Models\AuditLogs(['category' => 'announcement']);
        $this->assertEquals('fa-bullhorn', $log->getIcon());

        $log = new \App\Models\AuditLogs(['category' => 'system']);
        $this->assertEquals('fa-cog', $log->getIcon());
    }

    public function test_audit_logs_model_returns_correct_color_based_on_category(): void
    {
        $log = new \App\Models\AuditLogs(['category' => 'moderation']);
        $this->assertEquals('#ef4444', $log->getColor());
        
        $log = new \App\Models\AuditLogs(['category' => 'success']);
        $this->assertEquals('#22c55e', $log->getColor());

        $log = new \App\Models\AuditLogs(['category' => 'announcement']);
        $this->assertEquals('#3b82f6', $log->getColor());

        $log = new \App\Models\AuditLogs(['category' => 'system']);
        $this->assertEquals('#6b7280', $log->getColor());
    }

    public function test_audit_logs_factory_populates_category(): void
    {
        // We need a user for the factory to work if user_id is required
        \App\Models\User::factory()->create();
        
        $log = \App\Models\AuditLogs::factory()->create();
        $this->assertContains($log->category, ['moderation', 'success', 'announcement', 'system']);
    }
}
