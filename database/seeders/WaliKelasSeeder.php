<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WaliKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create users first
        $users = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.walikelas@school.test',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.walikelas@school.test',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.walikelas@school.test',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            $userId = DB::table('users')->insertGetId($user);
            
            // Assign role Wali Kelas
            DB::table('model_has_roles')->insert([
                'role_id' => DB::table('roles')->where('name', 'Wali Kelas')->first()->id,
                'model_type' => 'App\Models\User',
                'model_id' => $userId,
            ]);
        }

        // Insert wali kelas data
        DB::table('wali_kelas')->insert([
            [
                'user_id' => DB::table('users')->where('email', 'budi.walikelas@school.test')->first()->id,
                'kelas_id' => 1, // Pastikan kelas_id sudah ada di tabel kelas
                'nip' => '199001012020011001',
                'nama_lengkap' => 'Budi Santoso',
                'foto' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => DB::table('users')->where('email', 'siti.walikelas@school.test')->first()->id,
                'kelas_id' => 2,
                'nip' => '199103152020012002',
                'nama_lengkap' => 'Siti Aminah',
                'foto' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => DB::table('users')->where('email', 'ahmad.walikelas@school.test')->first()->id,
                'kelas_id' => 3,
                'nip' => '199208202020013003',
                'nama_lengkap' => 'Ahmad Rizki',
                'foto' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
