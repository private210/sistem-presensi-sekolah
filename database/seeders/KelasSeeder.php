<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $kelasData = [
            [
                'nama_kelas' => 'Kelas 1',
                'tahun_ajaran' => 2025/2026,
                'is_active' => true,
            ],
            [
                'nama_kelas' => 'Kelas 2',
                'tahun_ajaran' => 2025/2026,
                'is_active' => true,
            ],
            [
                'nama_kelas' => 'Kelas 3',
                'tahun_ajaran' => 2025/2026,
                'is_active' => true,
            ],
            [
                'nama_kelas' => 'Kelas 4',
                'tahun_ajaran' => 2025/2026,
                'is_active' => true,
            ],
            [
                'nama_kelas' => 'Kelas 5',
                'tahun_ajaran' => 2025/2026,
                'is_active' => true,
            ],
            [
                'nama_kelas' => 'Kelas 6',
                'tahun_ajaran' => 2025/2026,
                'is_active' => true,
            ],
        ];

        foreach ($kelasData as $kelas) {
            Kelas::create($kelas);
        }
    }
}
