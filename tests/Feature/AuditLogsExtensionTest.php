<?php

namespace Tests\Feature;

use App\Models\AuditLogs;
use App\Models\User;
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

    public function test_audit_logs_model_returns_correct_color_based_on_category(): void
    {
        $log = new AuditLogs(['category' => 'moderation']);
        $this->assertEquals('#ef4444', $log->getColor());

        $log = new AuditLogs(['category' => 'check']);
        $this->assertEquals('#3b82f6', $log->getColor());

        $log = new AuditLogs(['category' => 'announcement']);
        $this->assertEquals('#7c3aed', $log->getColor());

        $log = new AuditLogs(['category' => 'resolved']);
        $this->assertEquals('#10b981', $log->getColor());
    }

    public function test_audit_logs_factory_populates_category(): void
    {
        // We need a user for the factory to work if user_id is required
        User::factory()->create();

        $log = AuditLogs::factory()->create();
        $this->assertContains($log->category, ['moderation', 'success', 'announcement', 'system']);
    }
}
