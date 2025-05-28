<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Izin;
use App\Models\Siswa;
use App\Models\Presensi;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DashboardWaliKelasStats extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        $waliKelas = $user->waliKelas;

        if (!$waliKelas) {
            return [
                Stat::make('Error', 'Tidak ada kelas yang ditetapkan')
                    ->description('Hubungi administrator')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        $kelasId = $waliKelas->kelas_id;
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Data Siswa di Kelas
        $totalSiswa = Siswa::where('kelas_id', $kelasId)
            ->where('is_active', true)
            ->count();

        // Presensi Hari Ini
        $presensiHariIni = Presensi::where('kelas_id', $kelasId)
            ->whereDate('tanggal_presensi', $today)
            ->count();

        $hadirHariIni = Presensi::where('kelas_id', $kelasId)
            ->whereDate('tanggal_presensi', $today)
            ->where('status', 'Hadir')
            ->count();

        $belumPresensi = $totalSiswa - $presensiHariIni;
        $persentaseHadirHariIni = $presensiHariIni > 0 ? round(($hadirHariIni / $presensiHariIni) * 100, 1) : 0;

        // Presensi Minggu Ini
        $hadirMingguIni = Presensi::where('kelas_id', $kelasId)
            ->whereBetween('tanggal_presensi', [$startOfWeek, $endOfWeek])
            ->where('status', 'Hadir')
            ->count();

        $totalPresensiMingguIni = Presensi::where('kelas_id', $kelasId)
            ->whereBetween('tanggal_presensi', [$startOfWeek, $endOfWeek])
            ->count();

        // Presensi Bulan Ini
        $hadirBulanIni = Presensi::where('kelas_id', $kelasId)
            ->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->where('status', 'Hadir')
            ->count();

        $izinBulanIni = Presensi::where('kelas_id', $kelasId)
            ->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->where('status', 'Izin')
            ->count();

        $sakitBulanIni = Presensi::where('kelas_id', $kelasId)
            ->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->where('status', 'Sakit')
            ->count();

        $alphaBulanIni = Presensi::where('kelas_id', $kelasId)
            ->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->where('status', 'Tanpa Keterangan')
            ->count();

        $totalPresensi = $hadirBulanIni + $izinBulanIni + $sakitBulanIni + $alphaBulanIni;
        $persentaseKehadiran = $totalPresensi > 0 ? round(($hadirBulanIni / $totalPresensi) * 100, 1) : 0;

        // Izin yang menunggu persetujuan
        $izinMenunggu = Izin::whereHas('siswas', function ($query) use ($kelasId) {
            $query->where('kelas_id', $kelasId);
        })
            ->where('status', 'Menunggu')
            ->count();

        // Nama Kelas
        $namaKelas = $waliKelas->kelas->nama_kelas ?? 'Tidak Diketahui';

        return [
            // Informasi Kelas
            Stat::make('Informasi ' . $namaKelas, $totalSiswa . ' Siswa')
                ->description('Total siswa aktif di kelas')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            // Presensi Hari Ini
            Stat::make('Presensi Hari Ini', $presensiHariIni . '/' . $totalSiswa)
                ->description($belumPresensi > 0 ?
                    "Belum presensi: {$belumPresensi} siswa" :
                    "Semua siswa sudah presensi ({$persentaseHadirHariIni}% hadir)")
                ->descriptionIcon($belumPresensi > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($belumPresensi > 0 ? 'warning' : 'success')
                ->extraAttributes([
                    'wire:click' => '$dispatch("openModal", { component: "presensi-harian" })',
                    'class' => 'cursor-pointer hover:bg-gray-50',
                ]),
            // Izin Menunggu Persetujuan
            Stat::make('Izin Menunggu', $izinMenunggu)
                ->description($izinMenunggu > 0 ? 'Perlu review segera' : 'Semua izin sudah diproses')
                ->descriptionIcon($izinMenunggu > 0 ? 'heroicon-m-document-text' : 'heroicon-m-check-circle')
                ->color($izinMenunggu > 0 ? 'warning' : 'success')
                ->url($izinMenunggu > 0 ? route('filament.admin.pages.konfirmasi-izin-wali-kelas') : null)
                ->extraAttributes([
                    'class' => $izinMenunggu > 0 ? 'cursor-pointer hover:bg-gray-50' : '',
                ]),
            // Kehadiran Minggu Ini
            Stat::make('Kehadiran Minggu Ini', $hadirMingguIni)
                ->description("Dari {$totalPresensiMingguIni} total presensi")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($totalPresensiMingguIni > 0 && ($hadirMingguIni / $totalPresensiMingguIni) >= 0.8 ? 'success' : 'warning'),

            // Kehadiran Bulan Ini
            Stat::make('Kehadiran Bulan Ini', $hadirBulanIni)
                ->description("Persentase: {$persentaseKehadiran}%")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($persentaseKehadiran >= 80 ? 'success' : ($persentaseKehadiran >= 70 ? 'warning' : 'danger')),

            // Ketidakhadiran Bulan Ini
            Stat::make('Ketidakhadiran', $izinBulanIni + $sakitBulanIni + $alphaBulanIni)
                ->description("Izin: {$izinBulanIni} | Sakit: {$sakitBulanIni} | Alpha: {$alphaBulanIni}")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($alphaBulanIni > 0 ? 'danger' : 'info'),
        ];
    }

    protected function getColumns(): int
    {
        return 3; // Menampilkan 3 kolom per baris
    }
}
