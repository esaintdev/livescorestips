<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AdminUser;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@livescorestips.com',
            'password' => 'Admin@2024!', // Will be hashed automatically
            'role' => 'super_admin',
        ]);
    }
}
