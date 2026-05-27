<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FontsSeeder::class,
        ]);

        $admin = User::query()->firstOrNew([
            'email' => 'anthtooaung2792005@gmail.com',
        ]);

        $admin->forceFill([
            'name' => 'Ant Htoo Aung',
            'role' => 'admin',
            'user_dob' => '2005-09-27',
            'user_image' => null,
            'is_verified' => true,
            'password' => 'admin123!@#',
        ])->save();

        $admin->provisionDefaultPreferences();
    }
}
