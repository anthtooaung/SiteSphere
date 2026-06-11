<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrInsert(
            ['email' => 'anthtooaung2792005@outlook.com'],
            [
                'name' => 'Admin',
                'slug' => Str::slug('Admin'),
                'role' => 'admin',
                'password' => Hash::make('123!@#123'),
                'is_verified' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
