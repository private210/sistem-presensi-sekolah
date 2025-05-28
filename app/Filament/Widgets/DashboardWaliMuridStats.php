<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Izin;
use App\Models\Presensi;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DashboardWaliMuridStats extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();
        $waliMurid = $user->waliMurid;

        if (!$waliMurid || !$waliMurid->siswa) {
            return [
                Stat::make('Error', 'Tidak ada siswa yang terdaftar')
                    ->description('Hubungi administrator')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        $siswa = $waliMurid->siswa;
        $siswaId = $siswa->id;
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // Informasi Siswa
        $namaSiswa = $siswa->nama_lengkap;
        $namaKelas = $siswa->kelas->nama_kelas ?? 'Tidak Diketahui';
        $nis = $siswa->nis;

        // Presensi Hari Ini
        $presensiHariIni = Presensi::where('siswa_id', $siswaId)
            ->whereDate('tanggal_presensi', $today)
            ->first();

        $statusHariIni = $presensiHariIni ? $presensiHariIni->status : 'Belum Presensi';

        // Presensi Minggu Ini
        $presensiMingguIni = Presensi::where('siswa_id', $siswaId)
            ->whereBetween('tanggal_presensi', [$startOfWeek, $endOfWeek])
            ->get();

        $hadirMingguIni = $presensiMingguIni->where('status', 'Hadir')->count();
        $totalHariMingguIni = $presensiMingguIni->count();

        // Presensi Bulan Ini
        $presensiBulanIni = Presensi::where('siswa_id', $siswaId)
            ->whereBetween('tanggal_presensi', [$startOfMonth, $endOfMonth])
            ->get();

        $hadirBulanIni = $presensiBulanIni->where('status', 'Hadir')->count();
        $izinBulanIni = $presensiBulanIni->where('status', 'Izin')->count();
        $sakitBulanIni = $presensiBulanIni->where('status', 'Sakit')->count();
        $alphaBulanIni = $presensiBulanIni->where('status', 'Tanpa Keterangan')->count();
        $totalHariBulanIni = $presensiBulanIni->count();

        // Persentase Kehadiran
        $persentaseKehadiranMingguIni = $totalHariMingguIni > 0 ?
            round(($hadirMingguIni / $totalHariMingguIni) * 100, 1) : 0;

        $persentaseKehadiranBulanIni = $totalHariBulanIni > 0 ?
            round(($hadirBulanIni / $totalHariBulanIni) * 100, 1) : 0;

        // Status Izin
        $izinAktif = Izin::where('siswa_id', $siswaId)
            ->where('status', 'Disetujui')
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->exists();

        $izinMenunggu = Izin::where('siswa_id', $siswaId)
            ->where('status', 'Menunggu')
            ->count();

        $izinDitolak = Izin::where('siswa_id', $siswaId)
            ->where('status', 'Ditolak')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        // Trend kehadiran (perbandingan dengan bulan lalu)
        $bulanLalu = Carbon::now()->subMonth();
        $hadirBulanLalu = Presensi::where('siswa_id', $siswaId)
            ->whereYear('tanggal_presensi', $bulanLalu->year)
            ->whereMonth('tanggal_presensi', $bulanLalu->month)
            ->where('status', 'Hadir')
            ->count();

        $totalBulanLalu = Presensi::where('siswa_id', $siswaId)
            ->whereYear('tanggal_presensi', $bulanLalu->year)
            ->whereMonth('tanggal_presensi', $bulanLalu->month)
            ->count();

        $persentaseBulanLalu = $totalBulanLalu > 0 ?
            round(($hadirBulanLalu / $totalBulanLalu) * 100, 1) : 0;

        $trendKehadiran = $persentaseKehadiranBulanIni - $persentaseBulanLalu;

        return [
            // Informasi Siswa
            Stat::make($namaSiswa, "{$namaKelas}")
                ->description("NIS: {$nis}")
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),

            // Status Hari Ini
            Stat::make('Status Hari Ini', $statusHariIni)
                ->description($presensiHariIni && $presensiHariIni->keterangan ?
                    substr($presensiHariIni->keterangan, 0, 30) . '...' :
                    'Status kehadiran hari ini')
                ->descriptionIcon(match ($statusHariIni) {
                    'Hadir' => 'heroicon-m-check-circle',
                    'Izin' => 'heroicon-m-document-text',
                    'Sakit' => 'heroicon-m-heart',
                    'Tanpa Keterangan' => 'heroicon-m-x-circle',
                    default => 'heroicon-m-clock'
                })
                ->color(match ($statusHariIni) {
                    'Hadir' => 'success',
                    'Izin' => 'info',
                    'Sakit' => 'warning',
                    'Tanpa Keterangan' => 'danger',
                    default => 'gray'
                }),

            // Status Izin
            Stat::make(
                'Status Izin',
                $izinAktif ? 'Sedang Izin' : ($izinMenunggu > 0 ? "{$izinMenunggu} Menunggu" : 'Tidak Ada')
            )
                ->description($izinDitolak > 0 ?
                    "{$izinDitolak} izin ditolak bulan ini" : ($izinAktif ? 'Izin sedang berlangsung' : 'Semua izin sudah diproses'))
                ->descriptionIcon($izinAktif ? 'heroicon-m-pause-circle' : ($izinMenunggu > 0 ? 'heroicon-m-clock' : ($izinDitolak > 0 ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')))
                ->color($izinAktif ? 'info' : ($izinMenunggu > 0 ? 'warning' : ($izinDitolak > 0 ? 'danger' : 'success')))
                ->url(route('filament.admin.pages.pengajuan-izin-wali-murid'))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:bg-gray-50',
                ]),

            // Kehadiran Minggu Ini
            Stat::make('Kehadiran Minggu Ini', "{$hadirMingguIni}/{$totalHariMingguIni}")
                ->description("Persentase: {$persentaseKehadiranMingguIni}%")
                ->descriptionIcon($persentaseKehadiranMingguIni >= 80 ?
                    'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($persentaseKehadiranMingguIni >= 80 ? 'success' : ($persentaseKehadiranMingguIni >= 70 ? 'warning' : 'danger')),

            // Kehadiran Bulan Ini
            Stat::make('Kehadiran Bulan Ini', "{$hadirBulanIni}/{$totalHariBulanIni}")
                ->description("Persentase: {$persentaseKehadiranBulanIni}%" .
                    ($trendKehadiran != 0 ?
                        " (" . ($trendKehadiran > 0 ? '+' : '') . round($trendKehadiran, 1) . "% dari bulan lalu)" :
                        ""))
                ->descriptionIcon($trendKehadiran >= 0 ?
                    'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($persentaseKehadiranBulanIni >= 80 ? 'success' : ($persentaseKehadiranBulanIni >= 70 ? 'warning' : 'danger')),

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
