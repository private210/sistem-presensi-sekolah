<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HariLiburSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama jika ada
        HariLibur::truncate();

        $hariLibur = [
            [
                'nama_hari_libur' => 'Tahun Baru 2025',
                'tanggal_mulai' => '2025-01-01',
                'tanggal_selesai' => null, // atau bisa diisi jika hari libur lebih dari 1 hari
                'keterangan' => 'Libur Tahun Baru 2025',
            ],
            [
                'nama_hari_libur' => 'Tahun Baru Imlek 2576',
                'tanggal_mulai' => '2025-01-29',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Tahun Baru Imlek 2576 Kongzili',
            ],
            [
                'nama_hari_libur' => 'Isra Miraj Nabi Muhammad SAW',
                'tanggal_mulai' => '2025-02-19',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Isra Miraj Nabi Muhammad SAW',
            ],
            [
                'nama_hari_libur' => 'Hari Raya Nyepi',
                'tanggal_mulai' => '2025-03-29',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Hari Raya Nyepi Tahun Baru Saka 1947',
            ],
            [
                'nama_hari_libur' => 'Wafat Isa Al Masih',
                'tanggal_mulai' => '2025-04-18',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Wafat Isa Al Masih',
            ],
            [
                'nama_hari_libur' => 'Hari Buruh Internasional',
                'tanggal_mulai' => '2025-05-01',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Hari Buruh Internasional',
            ],
            [
                'nama_hari_libur' => 'Kenaikan Isa Al Masih',
                'tanggal_mulai' => '2025-05-29',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Kenaikan Isa Al Masih',
            ],
            [
                'nama_hari_libur' => 'Hari Raya Idul Fitri 1446 H',
                'tanggal_mulai' => '2025-06-09',
                'tanggal_selesai' => '2025-06-10', // Biasanya Idul Fitri libur 2 hari
                'keterangan' => 'Libur Hari Raya Idul Fitri 1446 H',
            ],
            [
                'nama_hari_libur' => 'Hari Raya Idul Adha 1446 H',
                'tanggal_mulai' => '2025-06-17',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Hari Raya Idul Adha 1446 H',
            ],
            [
                'nama_hari_libur' => 'Tahun Baru Islam 1447 H',
                'tanggal_mulai' => '2025-07-07',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Tahun Baru Islam 1447 H',
            ],
            [
                'nama_hari_libur' => 'Hari Kemerdekaan Republik Indonesia',
                'tanggal_mulai' => '2025-08-17',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Hari Kemerdekaan Republik Indonesia',
            ],
            [
                'nama_hari_libur' => 'Maulid Nabi Muhammad SAW',
                'tanggal_mulai' => '2025-09-07',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Maulid Nabi Muhammad SAW',
            ],
            [
                'nama_hari_libur' => 'Hari Natal',
                'tanggal_mulai' => '2025-12-25',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Hari Natal',
            ],
            [
                'nama_hari_libur' => 'Tahun Baru 2026',
                'tanggal_mulai' => '2026-01-01',
                'tanggal_selesai' => null,
                'keterangan' => 'Libur Tahun Baru 2026',
            ],
        ];

        foreach ($hariLibur as $libur) {
            HariLibur::create($libur);
        }
    }
}
