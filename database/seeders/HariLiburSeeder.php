<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HariLiburSeeder extends Seeder
{
    public function run(): void
    {
        $hariLibur = [
            [
                'nama_hari_libur' => 'Tahun Baru 2025',
                'tanggal' => '2025-01-01',
                'keterangan' => 'Libur Tahun Baru 2025',
            ],
            [
                'nama_hari_libur' => 'Tahun Baru Imlek 2576',
                'tanggal' => '2025-01-29',
                'keterangan' => 'Libur Tahun Baru Imlek 2576 Kongzili',
            ],
            [
                'nama_hari_libur' => 'Isra Miraj Nabi Muhammad SAW',
                'tanggal' => '2025-02-19',
                'keterangan' => 'Libur Isra Miraj Nabi Muhammad SAW',
            ],
            [
                'nama_hari_libur' => 'Hari Raya Nyepi',
                'tanggal' => '2025-03-29',
                'keterangan' => 'Libur Hari Raya Nyepi Tahun Baru Saka 1947',
            ],
            [
                'nama_hari_libur' => 'Wafat Isa Al Masih',
                'tanggal' => '2025-04-18',
                'keterangan' => 'Libur Wafat Isa Al Masih',
            ],
            [
                'nama_hari_libur' => 'Hari Buruh Internasional',
                'tanggal' => '2025-05-01',
                'keterangan' => 'Libur Hari Buruh Internasional',
            ],
            [
                'nama_hari_libur' => 'Kenaikan Isa Al Masih',
                'tanggal' => '2025-05-29',
                'keterangan' => 'Libur Kenaikan Isa Al Masih',
            ],
            [
                'nama_hari_libur' => 'Hari Raya Idul Fitri 1446 H',
                'tanggal' => '2025-06-09',
                'keterangan' => 'Libur Hari Raya Idul Fitri 1446 H',
            ],
            [
                'nama_hari_libur' => 'Hari Raya Idul Adha 1446 H',
                'tanggal' => '2025-06-17',
                'keterangan' => 'Libur Hari Raya Idul Adha 1446 H',
            ],
            [
                'nama_hari_libur' => 'Tahun Baru Islam 1447 H',
                'tanggal' => '2025-07-07',
                'keterangan' => 'Libur Tahun Baru Islam 1447 H',
            ],
            [
                'nama_hari_libur' => 'Hari Kemerdekaan Republik Indonesia',
                'tanggal' => '2025-08-17',
                'keterangan' => 'Libur Hari Kemerdekaan Republik Indonesia',
            ],
            [
                'nama_hari_libur' => 'Maulid Nabi Muhammad SAW',
                'tanggal' => '2025-09-07',
                'keterangan' => 'Libur Maulid Nabi Muhammad SAW',
            ],
            [
                'nama_hari_libur' => 'Hari Natal',
                'tanggal' => '2025-12-25',
                'keterangan' => 'Libur Hari Natal',
            ],
            [
                'nama_hari_libur' => 'Tahun Baru 2026',
                'tanggal' => '2026-01-01',
                'keterangan' => 'Libur Tahun Baru 2026',
            ],
        ];

        foreach ($hariLibur as $libur) {
            HariLibur::create($libur);
        }
    }
}
