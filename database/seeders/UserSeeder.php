<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('admin123'),
        ]);
        $superAdmin->assignRole('super_admin');

        // Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Admin');

        // Kepala Sekolah
        $kepalaSekolah = User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepalasekolah@example.com',
            'password' => Hash::make('kepsek123'),
        ]);
        $kepalaSekolah->assignRole('Kepala Sekolah');
    }
}
