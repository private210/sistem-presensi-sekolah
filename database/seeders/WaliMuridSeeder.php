<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WaliMuridSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users first
        $users = [
            [
                'name' => 'Ahmad Suhendra',
                'email' => 'ahmad.walimurid@school.test',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Siti Fatimah',
                'email' => 'siti.walimurid@school.test',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi.walimurid@school.test',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            $userId = DB::table('users')->insertGetId($user);
            
            // Assign role Wali Murid
            DB::table('model_has_roles')->insert([
                'role_id' => DB::table('roles')->where('name', 'Wali Murid')->first()->id,
                'model_type' => 'App\Models\User',
                'model_id' => $userId,
            ]);
        }

        // Insert wali murid data
        DB::table('wali_murids')->insert([
            [
                'user_id' => DB::table('users')->where('email', 'ahmad.walimurid@school.test')->first()->id,
                'siswa_id' => 1, // Pastikan siswa_id sudah ada di tabel siswas
                'nama_lengkap' => 'Ahmad Suhendra',
                'hubungan' => 'Ayah',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => DB::table('users')->where('email', 'siti.walimurid@school.test')->first()->id,
                'siswa_id' => 2,
                'nama_lengkap' => 'Siti Fatimah',
                'hubungan' => 'Ibu',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => DB::table('users')->where('email', 'dedi.walimurid@school.test')->first()->id,
                'siswa_id' => 3,
                'nama_lengkap' => 'Dedi Kurniawan',
                'hubungan' => 'Wali',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
