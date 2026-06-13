# Audit Logs Extension Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Extend the `audit_logs` table and model to support event categories and UI metadata.

**Architecture:** Add a `category` column via migration, implement helper methods on the model for UI attributes (icons, colors), and update the factory for testing.

**Tech Stack:** Laravel 13, PHP 8.3, SQLite/MySQL.

---

### Task 1: Test Setup & Migration

**Files:**
- Create: `tests/Feature/AuditLogsExtensionTest.php`
- Create: `database/migrations/2026_06_14_000000_add_category_to_audit_logs_table.php`

- [ ] **Step 1: Write the failing test for migration**

```php
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
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AuditLogsExtensionTest.php`
Expected: FAIL (Column 'category' not found)

- [ ] **Step 3: Create and implement the migration**

```php
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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('category')->default('system')->after('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/AuditLogsExtensionTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/AuditLogsExtensionTest.php database/migrations/2026_06_14_000000_add_category_to_audit_logs_table.php
git commit -m "feat: add category column to audit_logs table"
```

### Task 2: Model Extensions

**Files:**
- Modify: `app/Models/AuditLogs.php`

- [ ] **Step 1: Write failing tests for model methods**

```php
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
        $this->assertNotEmpty($log->getColor());
        
        $log = new \App\Models\AuditLogs(['category' => 'success']);
        $this->assertNotEmpty($log->getColor());
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AuditLogsExtensionTest.php`
Expected: FAIL (Method getIcon/getColor not defined)

- [ ] **Step 3: Implement model methods**

```php
    public function getIcon(): string
    {
        return match ($this->category) {
            'moderation' => 'fa-hammer',
            'success' => 'fa-check-circle',
            'announcement' => 'fa-bullhorn',
            default => 'fa-cog',
        };
    }

    public function getColor(): string
    {
        return match ($this->category) {
            'moderation' => '#ef4444', // Red-500
            'success' => '#22c55e',    // Green-500
            'announcement' => '#3b82f6', // Blue-500
            default => '#6b7280',      // Gray-500
        };
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/AuditLogsExtensionTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Models/AuditLogs.php
git commit -m "feat: add getIcon and getColor methods to AuditLogs model"
```

### Task 3: Factory Update

**Files:**
- Modify: `database/factories/AuditLogsFactory.php`

- [ ] **Step 1: Write test to verify factory uses categories**

```php
    public function test_audit_logs_factory_populates_category(): void
    {
        $log = \App\Models\AuditLogs::factory()->create();
        $this->assertContains($log->category, ['moderation', 'success', 'announcement', 'system']);
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AuditLogsExtensionTest.php`
Expected: FAIL (category is always 'system' or fails if not in fillable)

- [ ] **Step 3: Update factory definition**

```php
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'action' => $this->faker->word(),
            'category' => $this->faker->randomElement(['moderation', 'success', 'announcement', 'system']),
            'target_type' => 'App\Models\Post',
            'target_id' => $this->faker->randomNumber(),
            'reason' => $this->faker->sentence(),
        ];
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/AuditLogsExtensionTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/factories/AuditLogsFactory.php
git commit -m "test: update AuditLogsFactory with new categories"
```
