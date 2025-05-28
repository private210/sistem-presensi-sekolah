<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class RekapPresensi extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Rekap Presensi Sekolah';
    protected static ?string $navigationTitle = 'Rekap Presensi Kepala Sekolah';
    protected static ?string $navigationGroup = 'Kepala Sekolah';
    protected static string $view = 'filament.pages.rekap-presensi';
    protected static ?int $navigationSort = 3;

    // Form properties
    public $tanggal_mulai;
    public $tanggal_selesai;
    public $kelas_id;
    public $periode = 'bulan_ini';

    // Data properties
    public $rekap_data = [];
    public $total_kehadiran = [];
    public $statistik_per_kelas = [];
    public $statistik_bulanan = [];
    public $siswa_persentase = [];

    public function mount(): void
    {
        // Set default date range to current month
        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Initialize the form
        $this->form->fill([
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'periode' => $this->periode,
            'kelas_id' => $this->kelas_id,
        ]);

        // Generate initial report
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Laporan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('periode')
                                    ->label('Periode')
                                    ->options([
                                        'hari_ini' => 'Hari Ini',
                                        'minggu_ini' => 'Minggu Ini',
                                        'bulan_ini' => 'Bulan Ini',
                                        'semester_ini' => 'Semester Ini',
                                        'kustom' => 'Kustom',
                                    ])
                                    ->default('bulan_ini')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->periode = $state;
                                        $this->updateDateRange($state);
                                        $this->generateReport();
                                    }),

                                DatePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->visible(fn($get) => $get('periode') === 'kustom')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->tanggal_mulai = $state;
                                        if ($this->periode === 'kustom') {
                                            $this->generateReport();
                                        }
                                    }),

                                DatePicker::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->visible(fn($get) => $get('periode') === 'kustom')
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->tanggal_selesai = $state;
                                        if ($this->periode === 'kustom') {
                                            $this->generateReport();
                                        }
                                    }),

                                Select::make('kelas_id')
                                    ->label('Kelas')
                                    ->relationship('kelas', 'nama_kelas')
                                    ->options(function () {
                                        if (auth()->user()->hasRole('Wali Kelas')) {
                                            $waliKelas = auth()->user()->waliKelas;
                                            if ($waliKelas) {
                                                return Kelas::where('id', $waliKelas->kelas_id)
                                                    ->pluck('nama_kelas', 'id');
                                            }
                                            return collect();
                                        }

                                        return Kelas::pluck('nama_kelas', 'id');
                                    })
                                    ->placeholder('Semua Kelas')
                                    // ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->kelas_id = $state;
                                        $this->generateReport();
                                    }),
                            ]),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    protected function updateDateRange($periode): void
    {
        switch ($periode) {
            case 'hari_ini':
                $this->tanggal_mulai = Carbon::today()->format('Y-m-d');
                $this->tanggal_selesai = Carbon::today()->format('Y-m-d');
                break;
            case 'minggu_ini':
                $this->tanggal_mulai = Carbon::now()->startOfWeek()->format('Y-m-d');
                $this->tanggal_selesai = Carbon::now()->endOfWeek()->format('Y-m-d');
                break;
            case 'bulan_ini':
                $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'semester_ini':
                // Assuming first semester is July-December, second is January-June
                $currentMonth = Carbon::now()->month;
                if ($currentMonth >= 7) {
                    $this->tanggal_mulai = Carbon::now()->year . '-07-01';
                    $this->tanggal_selesai = Carbon::now()->year . '-12-31';
                } else {
                    $this->tanggal_mulai = Carbon::now()->year . '-01-01';
                    $this->tanggal_selesai = Carbon::now()->year . '-06-30';
                }
                break;
        }

        // Update form dengan tanggal yang baru
        $this->form->fill([
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'periode' => $this->periode,
            'kelas_id' => $this->kelas_id,
        ]);
    }

    public function generateReport(): void
    {
        // Pastikan tanggal tidak kosong
        if (empty($this->tanggal_mulai) || empty($this->tanggal_selesai)) {
            return;
        }

        // Generate overall statistics
        $this->generateTotalKehadiran();

        // Generate per-class statistics
        $this->generateStatistikPerKelas();

        // Generate monthly statistics
        $this->generateStatistikBulanan();

        // Generate student attendance percentage
        $this->generateSiswaPersentase();
    }

    private function generateTotalKehadiran(): void
    {
        $query = Presensi::query()
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);

        if (!empty($this->kelas_id)) {
            $query->where('kelas_id', $this->kelas_id);
        }

        // Filter by role access
        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            }
        }

        $this->total_kehadiran = [
            'total' => $query->count(),
            'hadir' => $query->clone()->where('status', 'Hadir')->count(),
            'izin' => $query->clone()->where('status', 'Izin')->count(),
            'sakit' => $query->clone()->where('status', 'Sakit')->count(),
            'alpha' => $query->clone()->where('status', 'Tanpa Keterangan')->count(),
        ];

        // Calculate percentages
        if ($this->total_kehadiran['total'] > 0) {
            $this->total_kehadiran['hadir_persen'] = round(($this->total_kehadiran['hadir'] / $this->total_kehadiran['total']) * 100, 2);
            $this->total_kehadiran['izin_persen'] = round(($this->total_kehadiran['izin'] / $this->total_kehadiran['total']) * 100, 2);
            $this->total_kehadiran['sakit_persen'] = round(($this->total_kehadiran['sakit'] / $this->total_kehadiran['total']) * 100, 2);
            $this->total_kehadiran['alpha_persen'] = round(($this->total_kehadiran['alpha'] / $this->total_kehadiran['total']) * 100, 2);
        } else {
            $this->total_kehadiran['hadir_persen'] = 0;
            $this->total_kehadiran['izin_persen'] = 0;
            $this->total_kehadiran['sakit_persen'] = 0;
            $this->total_kehadiran['alpha_persen'] = 0;
        }
    }

    private function generateStatistikPerKelas(): void
    {
        $query = Presensi::query()
            ->select('kelas_id', 'status', DB::raw('count(*) as total'))
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai])
            ->groupBy('kelas_id', 'status');

        if (!empty($this->kelas_id)) {
            $query->where('kelas_id', $this->kelas_id);
        }

        // Filter by role access
        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            }
        }

        $results = $query->get();

        // Get class names
        $kelas_ids = $results->pluck('kelas_id')->unique()->toArray();

        if (empty($kelas_ids)) {
            $this->statistik_per_kelas = [];
            return;
        }

        $kelas_names = Kelas::whereIn('id', $kelas_ids)->pluck('nama_kelas', 'id')->toArray();

        // Process and organize the data
        $data = [];
        foreach ($kelas_ids as $id) {
            $data[$id] = [
                'nama_kelas' => $kelas_names[$id] ?? 'Unknown',
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
                'total' => 0,
            ];
        }

        foreach ($results as $result) {
            $kelas_id = $result->kelas_id;
            $status = $result->status;
            $count = $result->total;

            if ($status === 'Hadir') {
                $data[$kelas_id]['hadir'] = $count;
            } elseif ($status === 'Izin') {
                $data[$kelas_id]['izin'] = $count;
            } elseif ($status === 'Sakit') {
                $data[$kelas_id]['sakit'] = $count;
            } elseif ($status === 'Tanpa Keterangan') {
                $data[$kelas_id]['alpha'] = $count;
            }

            $data[$kelas_id]['total'] += $count;
        }

        // Calculate percentages
        foreach ($data as $kelas_id => $stats) {
            if ($stats['total'] > 0) {
                $data[$kelas_id]['hadir_persen'] = round(($stats['hadir'] / $stats['total']) * 100, 2);
                $data[$kelas_id]['izin_persen'] = round(($stats['izin'] / $stats['total']) * 100, 2);
                $data[$kelas_id]['sakit_persen'] = round(($stats['sakit'] / $stats['total']) * 100, 2);
                $data[$kelas_id]['alpha_persen'] = round(($stats['alpha'] / $stats['total']) * 100, 2);
            } else {
                $data[$kelas_id]['hadir_persen'] = 0;
                $data[$kelas_id]['izin_persen'] = 0;
                $data[$kelas_id]['sakit_persen'] = 0;
                $data[$kelas_id]['alpha_persen'] = 0;
            }
        }

        $this->statistik_per_kelas = array_values($data);
    }

    private function generateStatistikBulanan(): void
{
    $start = Carbon::parse($this->tanggal_mulai);
    $end = Carbon::parse($this->tanggal_selesai);

    // Jika rentang lebih dari 3 bulan, rekap per bulan
    if ($start->diffInMonths($end) > 3) {
        $query = Presensi::query()
            ->select(
                DB::raw('YEAR(tanggal_presensi) as year'),
                DB::raw('MONTH(tanggal_presensi) as month'),
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai])
            ->groupBy('year', 'month', 'status')
            ->orderBy('year')
            ->orderBy('month');
    } else {
        // Jika <= 3 bulan, rekap per hari
        $query = Presensi::query()
            ->select(
                'tanggal_presensi',
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai])
            ->groupBy('tanggal_presensi', 'status')
            ->orderBy('tanggal_presensi');
    }

    if (!empty($this->kelas_id)) {
        $query->where('kelas_id', $this->kelas_id);
    }

    // Filter berdasarkan role
    if (auth()->user()->hasRole('Wali Kelas')) {
        $waliKelas = auth()->user()->waliKelas;
        if ($waliKelas) {
            $query->where('kelas_id', $waliKelas->kelas_id);
        }
    }

    $results = $query->get();
    $data = [];

    if ($start->diffInMonths($end) > 3) {
        // Proses data bulanan
        foreach ($results as $result) {
            $period = $result->year . '-' . str_pad($result->month, 2, '0', STR_PAD_LEFT);
            $label = Carbon::createFromDate($result->year, $result->month, 1)->format('M Y');

            if (!isset($data[$period])) {
                $data[$period] = [
                    'label' => $label,
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'alpha' => 0,
                    'total' => 0,
                ];
            }

            $status = $result->status;
            $count = $result->total;

            if ($status === 'Hadir') {
                $data[$period]['hadir'] += $count;
            } elseif ($status === 'Izin') {
                $data[$period]['izin'] += $count;
            } elseif ($status === 'Sakit') {
                $data[$period]['sakit'] += $count;
            } elseif ($status === 'Tanpa Keterangan') {
                $data[$period]['alpha'] += $count;
            }

            $data[$period]['total'] += $count;
        }
    } else {
        // Proses data harian
        foreach ($results as $result) {
            $tanggal = Carbon::parse($result->tanggal_presensi)->format('Y-m-d');

            if (!isset($data[$tanggal])) {
                $data[$tanggal] = [
                    'label' => Carbon::parse($tanggal)->translatedFormat('d M Y'),
                    'hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'alpha' => 0,
                    'total' => 0,
                ];
            }

            $status = $result->status;
            $count = $result->total;

            if ($status === 'Hadir') {
                $data[$tanggal]['hadir'] += $count;
            } elseif ($status === 'Izin') {
                $data[$tanggal]['izin'] += $count;
            } elseif ($status === 'Sakit') {
                $data[$tanggal]['sakit'] += $count;
            } elseif ($status === 'Tanpa Keterangan') {
                $data[$tanggal]['alpha'] += $count;
            }

            $data[$tanggal]['total'] += $count;
        }
    }

    // Sort by date
    ksort($data);

    // Calculate percentages for each period
    foreach ($data as $key => $stats) {
        if ($stats['total'] > 0) {
            $data[$key]['hadir_persen'] = round(($stats['hadir'] / $stats['total']) * 100, 2);
            $data[$key]['izin_persen'] = round(($stats['izin'] / $stats['total']) * 100, 2);
            $data[$key]['sakit_persen'] = round(($stats['sakit'] / $stats['total']) * 100, 2);
            $data[$key]['alpha_persen'] = round(($stats['alpha'] / $stats['total']) * 100, 2);
        } else {
            $data[$key]['hadir_persen'] = 0;
            $data[$key]['izin_persen'] = 0;
            $data[$key]['sakit_persen'] = 0;
            $data[$key]['alpha_persen'] = 0;
        }
    }

    $this->statistik_bulanan = array_values($data);
}
    //     foreach ($data as $key => $stats) {
    //         if ($stats['total'] > 0) {
    //             $data[$key]['hadir_persen'] = round(($stats['hadir'] / $stats['total']) * 100, 2);
    //             $data[$key]['izin_persen'] = round(($stats['izin'] / $stats['total']) * 100, 2);
    //             $data[$key]['sakit_persen'] = round(($stats['sakit'] / $stats['total']) * 100, 2);
    //             $data[$key]['alpha_persen'] = round(($stats['alpha'] / $stats['total']) * 100, 2);
    //         } else {
    //             $data[$key]['hadir_persen'] = 0;
    //             $data[$key]['izin_persen'] = 0;
    //             $data[$key]['sakit_persen'] = 0;
    //             $data[$key]['alpha_persen'] = 0;
    //         }
    //     }

    //     $this->statistik_bulanan = array_values($data);
    // }

    private function generateSiswaPersentase(): void
    {
        $siswaQuery = Siswa::query()
            ->with('kelas');

        if (!empty($this->kelas_id)) {
            $siswaQuery->where('kelas_id', $this->kelas_id);
        }

        // Filter by role access
        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $siswaQuery->where('kelas_id', $waliKelas->kelas_id);
            }
        }

        $siswa = $siswaQuery->where('is_active', true)->get();

        $data = [];
        foreach ($siswa as $s) {
            // Count attendance records for this student in the period
            $presensi = Presensi::where('siswa_id', $s->id)
                ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai])
                ->get();

            $hadir = $presensi->where('status', 'Hadir')->count();
            $izin = $presensi->where('status', 'Izin')->count();
            $sakit = $presensi->where('status', 'Sakit')->count();
            $alpha = $presensi->where('status', 'Tanpa Keterangan')->count();
            $total = $presensi->count();

            // Count working days in period (excluding weekends for attendance calculation)
            $start = Carbon::parse($this->tanggal_mulai);
            $end = Carbon::parse($this->tanggal_selesai);
            $totalWorkingDays = 0;

            while ($start <= $end) {
                if (!$start->isWeekend()) {
                    $totalWorkingDays++;
                }
                $start->addDay();
            }

            // Calculate attendance percentage based on actual attendance records vs working days
            $kehadiran = $totalWorkingDays > 0 ? ($total / $totalWorkingDays) * 100 : 0;

            // Only include if there's at least one attendance record
            if ($total > 0 || $totalWorkingDays > 0) {
                $data[] = [
                    'siswa_id' => $s->id,
                    'nama' => $s->nama_lengkap,
                    'kelas' => $s->kelas->nama_kelas ?? 'Unknown',
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpha' => $alpha,
                    'total' => $total,
                    'total_hari' => $totalWorkingDays,
                    'kehadiran' => round($kehadiran, 2),
                    'hadir_persen' => $total > 0 ? round(($hadir / $total) * 100, 2) : 0,
                    'izin_persen' => $total > 0 ? round(($izin / $total) * 100, 2) : 0,
                    'sakit_persen' => $total > 0 ? round(($sakit / $total) * 100, 2) : 0,
                    'alpha_persen' => $total > 0 ? round(($alpha / $total) * 100, 2) : 0,
                ];
            }
        }

        // Sort by attendance percentage (descending)
        usort($data, function ($a, $b) {
            return $b['kehadiran'] <=> $a['kehadiran'];
        });

        $this->siswa_persentase = $data;
    }

    public function getMaxWidthProperty(): MaxWidth
    {
        return MaxWidth::ExtraLarge;
    }
}
