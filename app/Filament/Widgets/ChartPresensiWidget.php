<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Presensi;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class ChartPresensiWidget extends ChartWidget
{
    use HasWidgetShield;
    protected static ?string $heading = 'Grafik Kehadiran Mingguan';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = auth()->user();

        // Get last 7 days
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $days->push(Carbon::now()->subDays($i));
        }

        $query = Presensi::query();

        // Filter based on role
        if ($user->hasRole('Wali Kelas')) {
            $waliKelas = $user->waliKelas;
            if ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            }
        } elseif ($user->hasRole('Wali Murid')) {
            $waliMurid = $user->waliMurid;
            if ($waliMurid) {
                $query->where('siswa_id', $waliMurid->siswa_id);
            }
        }

        $hadirData = [];
        $izinData = [];
        $sakitData = [];
        $alphaData = [];
        $labels = [];

        foreach ($days as $day) {
            $labels[] = $day->format('D, M j');

            $dayQuery = $query->clone()->whereDate('tanggal_presensi', $day);

            $hadirData[] = $dayQuery->clone()->where('status', 'Hadir')->count();
            $izinData[] = $dayQuery->clone()->where('status', 'Izin')->count();
            $sakitData[] = $dayQuery->clone()->where('status', 'Sakit')->count();
            $alphaData[] = $dayQuery->clone()->where('status', 'Tanpa Keterangan')->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $hadirData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Izin',
                    'data' => $izinData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Sakit',
                    'data' => $sakitData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Alpha',
                    'data' => $alphaData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}
