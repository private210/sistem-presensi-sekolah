<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Presensi;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DashboardKepalaSekolahStats extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Statistik untuk bulan ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $today = Carbon::today();

        // Total data sekolah
        $totalKelas = Kelas::where('is_active', true)->count();
        $totalSiswa = Siswa::where('is_active', true)->count();
        $totalWaliKelas = User::whereHas('roles', function ($query) {
            $query->where('name', 'Wali Kelas');
        })->count();
        $totalWaliMurid = User::whereHas('roles', function ($query) {
            $query->where('name', 'Wali Murid');
        })->count();

        // Statistik presensi bulan ini
        $totalPresensi = Presensi::whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])->count();
        $totalHadir = Presensi::whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->where('status', 'Hadir')->count();
        $totalIzin = Presensi::whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->where('status', 'Izin')->count();
        $totalSakit = Presensi::whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->where('status', 'Sakit')->count();
        $totalAlpha = Presensi::whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->where('status', 'Tanpa Keterangan')->count();

        // Statistik presensi hari ini
        $presensiHariIni = Presensi::whereDate('tanggal_presensi', $today)->count();
        $hadirHariIni = Presensi::whereDate('tanggal_presensi', $today)
            ->where('status', 'Hadir')->count();

        // Persentase kehadiran
        $persentaseKehadiran = $totalPresensi > 0 ? round(($totalHadir / $totalPresensi) * 100, 1) : 0;
        $persentaseHadirHariIni = $presensiHariIni > 0 ? round(($hadirHariIni / $presensiHariIni) * 100, 1) : 0;

        // Trend kehadiran (dibandingkan bulan lalu)
        $bulanLalu = Carbon::now()->subMonth();
        $totalHadirBulanLalu = Presensi::whereYear('tanggal_presensi', $bulanLalu->year)
            ->whereMonth('tanggal_presensi', $bulanLalu->month)
            ->where('status', 'Hadir')->count();

        $trendKehadiran = $totalHadirBulanLalu > 0 ?
            round((($totalHadir - $totalHadirBulanLalu) / $totalHadirBulanLalu) * 100, 1) : 0;

        return [
            // Data Sekolah
            Stat::make('Total Kelas Aktif', $totalKelas)
                ->description('Kelas yang sedang aktif')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Total Siswa', $totalSiswa)
                ->description('Siswa aktif terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Guru & Staff', $totalWaliKelas)
                ->description('Wali kelas aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            // Statistik Presensi Hari Ini
            Stat::make('Presensi Hari Ini', $presensiHariIni)
                ->description("Kehadiran: {$persentaseHadirHariIni}%")
                ->descriptionIcon($persentaseHadirHariIni >= 80 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($persentaseHadirHariIni >= 80 ? 'success' : 'warning'),

            // Statistik Presensi Bulan Ini
            Stat::make('Kehadiran Bulan Ini', $totalHadir)
                ->description("Dari {$totalPresensi} total presensi ({$persentaseKehadiran}%)")
                ->descriptionIcon($trendKehadiran >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($persentaseKehadiran >= 80 ? 'success' : ($persentaseKehadiran >= 70 ? 'warning' : 'danger'))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50',
                ]),

            Stat::make('Siswa Tidak Hadir', $totalIzin + $totalSakit + $totalAlpha)
                ->description("Izin: {$totalIzin} | Sakit: {$totalSakit} | Alpha: {$totalAlpha}")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalAlpha > ($totalIzin + $totalSakit) ? 'danger' : 'info'),
        ];
    }

    protected function getColumns(): int
    {
        return 3; // Menampilkan 3 kolom per baris
    }
}
