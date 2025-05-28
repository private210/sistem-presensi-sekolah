<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            'super_admin' => 'Administrator dengan akses penuh ke seluruh sistem',
            'Admin' => 'Administrator sekolah yang mengelola data',
            'Wali Kelas' => 'Wali kelas yang mengelola presensi siswa',
            'Wali Murid' => 'Orang tua/wali dari siswa',
            'Kepala Sekolah' => 'Kepala sekolah yang melihat laporan',
        ];

        foreach ($roles as $name => $description) {
            Role::create([
                'name' => $name,
                // 'description' => $description,
                'guard_name' => 'web',
            ]);
        }

        // Create permissions for resources
        // Ini akan otomatis dihasilkan oleh Filament Shield
    }
}
