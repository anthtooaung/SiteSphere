<?php

namespace Tests\Feature;

use Tests\TestCase;

class TableOverflowTest extends TestCase
{
    public function test_reports_table_is_scrollable()
    {
        $css = file_get_contents(base_path('resources/css/reports.css'));
        $this->assertStringContainsString('.reports-table-wrap {', $css);
        $this->assertStringContainsString('overflow-x: auto', $css);
    }

    public function test_users_table_is_scrollable()
    {
        $this->assertFileExists(base_path('resources/css/admin-users.css'));
        $css = file_get_contents(base_path('resources/css/admin-users.css'));
        $this->assertStringContainsString('.admin-users-table-wrap {', $css);
        $this->assertStringContainsString('overflow-x: auto', $css);
    }
}
