<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Definisikan jadwal command aplikasi.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Buat presensi otomatis untuk kemarin setiap jam 1 pagi
        $schedule->command('attendance:auto-create')
            ->dailyAt('01:00')
            ->timezone('Asia/Jakarta')
            ->appendOutputTo(storage_path('logs/attendance-auto-create.log'))
            ->emailOutputOnFailure('admin@example.com') // Ganti dengan email admin
            ->onFailure(function () {
                \Log::error('Pembuatan presensi otomatis gagal pada jam 01:00');
            });

        // Cek cadangan jam 7 pagi untuk memastikan
        $schedule->command('attendance:auto-create')
            ->dailyAt('07:00')
            ->timezone('Asia/Jakarta')
            ->appendOutputTo(storage_path('logs/attendance-auto-create.log'))
            ->onFailure(function () {
                \Log::error('Pembuatan presensi otomatis gagal pada jam 07:00');
            });

        // Opsional: Cek untuk hari ini jam 12 siang (jika diperlukan)
        $schedule->command('attendance:auto-create', [Carbon::today()->format('Y-m-d')])
            ->dailyAt('12:00')
            ->timezone('Asia/Jakarta')
            ->appendOutputTo(storage_path('logs/attendance-auto-create.log'));
    }
    /**
     * Daftarkan command untuk aplikasi.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
