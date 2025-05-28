<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AktivitasTerbaruWidget;
use App\Filament\Widgets\ChartPresensiWidget;
use App\Filament\Widgets\DashboardKepalaSekolahStats;
use App\Filament\Widgets\DashboardWaliKelasStats;
use App\Filament\Widgets\DashboardWaliMuridStats;
use App\Filament\Widgets\IzinPendingWidget;
use App\Filament\Widgets\SiswaRankingWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class CustomDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';
    protected static string $view = 'filament.pages.custom-dashboard';

    public function getWidgets(): array
    {
        $user = auth()->user();
        $widgets = [];

        // Tentukan widget berdasarkan role
        if ($user->hasRole('Kepala Sekolah')) {
            $widgets = [
                DashboardKepalaSekolahStats::class,
                ChartPresensiWidget::class,
                IzinPendingWidget::class,
                SiswaRankingWidget::class,
                AktivitasTerbaruWidget::class,
            ];
        } elseif ($user->hasRole('Wali Kelas')) {
            $widgets = [
                DashboardWaliKelasStats::class,
                ChartPresensiWidget::class,
                IzinPendingWidget::class,
                SiswaRankingWidget::class,
                AktivitasTerbaruWidget::class,
            ];
        } elseif ($user->hasRole('Wali Murid')) {
            $widgets = [
                DashboardWaliMuridStats::class,
                ChartPresensiWidget::class,
                AktivitasTerbaruWidget::class,
            ];
        } elseif ($user->hasRole('super_admin')|| $user->hasRole('Admin')) {
            $widgets = [
                DashboardKepalaSekolahStats::class,
                // DashboardWaliKelasStats::class,
                // DashboardWaliMuridStats::class,
                ChartPresensiWidget::class,
                IzinPendingWidget::class,
                SiswaRankingWidget::class,
                AktivitasTerbaruWidget::class,
            ];
        }

        return $widgets;
    }

    public function getColumns(): int | string | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
            'xl' => 4,
        ];
    }

    // Method untuk mendapatkan data widget yang aman
    public function getWidgetData(): array
    {
        $widgets = $this->getWidgets();
        $widgetData = [];

        foreach ($widgets as $widgetClass) {
            try {
                // Cek apakah widget class exists
                if (!class_exists($widgetClass)) {
                    \Log::warning("Widget class not found: {$widgetClass}");
                    continue;
                }

                $widgetData[] = [
                    'class' => $widgetClass,
                    'name' => class_basename($widgetClass),
                    'widget' => true,
                ];
            } catch (\Exception $e) {
                \Log::error("Error loading widget {$widgetClass}: " . $e->getMessage());

                // Tambahkan widget error ke array
                $widgetData[] = [
                    'class' => $widgetClass,
                    'name' => class_basename($widgetClass),
                    'widget' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $widgetData;
    }

    // Override methods untuk menghilangkan header default Filament
    public function getTitle(): string
    {
        return '';
    }

    public function getHeading(): string
    {
        return '';
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    // Method untuk mendapatkan custom heading yang akan digunakan di blade template
    public function getCustomHeading(): string
    {
        $user = auth()->user();
        $greeting = $this->getGreeting();
        if ($user->hasRole('Kepala Sekolah')) {
            return "{$greeting},Ibu Kepala Sekolah 👋👋";
        } elseif ($user->hasRole('Wali Kelas')) {
            $waliKelas = $user->waliKelas;
            $namaWali = $waliKelas?->nama_lengkap ?? $user->name;
            return "{$greeting},Bapak/Ibu {$namaWali} 👋👋";
        } elseif ($user->hasRole('Wali Murid')) {
            $waliMurid = $user->waliMurid;
            $namaWali = $waliMurid?->nama_lengkap ?? $user->name;
            return "{$greeting},Bapak/Ibu {$namaWali} 👋👋!";
        }


        return $greeting . ', ' . $user->name;
    }

    public function getCustomSubheading(): ?string
    {
        $user = auth()->user();

        if ($user->hasRole('Kepala Sekolah')) {
            return 'Pantau keseluruhan aktivitas sekolah dan kehadiran siswa.';
        } elseif ($user->hasRole('Wali Kelas')) {
            $waliKelas = $user->waliKelas;
            $namaKelas = $waliKelas?->kelas?->nama_kelas ?? 'Tidak Diketahui';
            $jumlahSiswa = $waliKelas?->kelas?->siswa()->where('is_active', true)->count() ?? 0;
            return "Kelola presensi dan pantau kehadiran {$jumlahSiswa} siswa di {$namaKelas}.";
        } elseif ($user->hasRole('Wali Murid')) {
            $waliMurid = $user->waliMurid;
            $namaSiswa = $waliMurid?->siswa?->nama_lengkap ?? 'Tidak Diketahui';
            $namaKelas = $waliMurid?->siswa?->kelas?->nama_kelas ?? 'Tidak Diketahui';
            return "Pantau kehadiran {$namaSiswa} di {$namaKelas}.";
        }

        return 'Selamat datang di Sistem Manajemen Presensi Sekolah.';
    }

    private function getGreeting(): string
    {
        $hour = now()->hour;

        if ($hour < 12) {
            return 'Selamat Pagi';
        } elseif ($hour < 15) {
            return 'Selamat Siang';
        } elseif ($hour < 18) {
            return 'Selamat Sore';
        } else {
            return 'Selamat Malam';
        }
    }
}

