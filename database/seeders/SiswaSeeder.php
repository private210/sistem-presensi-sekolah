<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        $kelasXA = Kelas::where('nama_kelas', 'Kelas 1')->first();
        $kelasXB = Kelas::where('nama_kelas', 'Kelas 2')->first();

        // Contoh data siswa untuk kelas X-A
        $siswaXA = [
            [
                'nis' => '0001',
                'nama_lengkap' => 'Andi Setiawan',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '2008-05-10',
                'alamat' => 'Jl. Merdeka No. 123, Jakarta',
                'is_active' => true,
            ],
            [
                'nis' => '0002',
                'nama_lengkap' => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '2008-07-15',
                'alamat' => 'Jl. Pahlawan No. 45, Jakarta',
                'is_active' => true,
            ],
            [
                'nis' => '0003',
                'nama_lengkap' => 'Citra Dewi',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '2008-03-22',
                'alamat' => 'Jl. Mawar No. 67, Jakarta',
                'is_active' => true,
            ],
            [
                'nis' => '0004',
                'nama_lengkap' => 'Dian Purnama',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '2008-11-30',
                'alamat' => 'Jl. Melati No. 89, Jakarta',
                'is_active' => true,
            ],
            [
                'nis' => '0005',
                'nama_lengkap' => 'Eko Prasetyo',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '2008-09-25',
                'alamat' => 'Jl. Anggrek No. 12, Jakarta',
                'is_active' => true,
            ],
        ];

        // Contoh data siswa untuk kelas X-B
        $siswaXB = [
            [
                'nis' => '0006',
                'nama_lengkap' => 'Fani Rahma',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '2008-02-18',
                'alamat' => 'Jl. Kamboja No. 34, Jakarta',
                'is_active' => true,
            ],
            [
                'nis' => '0007',
                'nama_lengkap' => 'Gilang Ramadhan',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '2008-08-12',
                'alamat' => 'Jl. Kenanga No. 56, Jakarta',
                'is_active' => true,
            ],
            [
                'nis' => '0008',
                'nama_lengkap' => 'Hani Salsabila',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '2008-04-05',
                'alamat' => 'Jl. Dahlia No. 78, Jakarta',
                'is_active' => true,
            ],
            [
                'nis' => '0009',
                'nama_lengkap' => 'Irfan Hakim',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '2008-06-20',
                'alamat' => 'Jl. Tulip No. 90, Jakarta',
                'is_active' => true,
            ],
            [
                'nis' => '0010',
                'nama_lengkap' => 'Jihan Aulia',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '2008-01-15',
                'alamat' => 'Jl. Teratai No. 23, Jakarta',
                'is_active' => true,
            ],
        ];

        // Insert data siswa kelas X-A
        foreach ($siswaXA as $siswa) {
            Siswa::create(array_merge($siswa, ['kelas_id' => $kelasXA->id]));
        }

        // Insert data siswa kelas X-B
        foreach ($siswaXB as $siswa) {
            Siswa::create(array_merge($siswa, ['kelas_id' => $kelasXB->id]));
        }
    }
}
