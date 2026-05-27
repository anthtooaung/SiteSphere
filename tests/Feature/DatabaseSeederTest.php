<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_the_admin_account(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', 'anthtooaung2792005@gmail.com')
            ->firstOrFail();

        $this->assertSame('Ant Htoo Aung', $admin->name);
        $this->assertSame('admin', $admin->role);
        $this->assertSame('2005-09-27', $admin->user_dob?->toDateString());
        $this->assertNull($admin->user_image);
        $this->assertTrue(Hash::check('admin123!@#', $admin->password));

        $this->assertDatabaseHas('settings', [
            'user_id' => $admin->id,
            'noti_location' => 'top-end',
            'menuBar_location' => 'left',
        ]);

        $this->assertDatabaseHas('user_current_fonts', [
            'user_id' => $admin->id,
            'font_id' => DB::table('fonts')->where('display_name', 'Inter')->value('id'),
        ]);
    }
}
