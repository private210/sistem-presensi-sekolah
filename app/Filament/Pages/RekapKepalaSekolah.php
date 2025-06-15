<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardKepalaSekolahStats;
use App\Models\Kelas;
use App\Models\Presensi;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RekapKepalaSekolah extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Rekap Presensi Sekolah';
    protected static ?string $title = 'Rekap Presensi Sekolah';
    protected static string $view = 'filament.pages.rekap-kepala-sekolah';
    protected static ?string $navigationGroup = 'Kepala Sekolah';
    protected static ?int $navigationSort = 1;

    public $tanggal_mulai;
    public $tanggal_selesai;
    public $kelas_id;

    public function mount(): void
    {
        if (!auth()->user()->hasRole('Kepala Sekolah') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->format('Y-m-d');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Presensi::query()
                    ->with(['siswa', 'kelas'])
                    ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai])
                    ->when($this->kelas_id, function ($query) {
                        return $query->where('kelas_id', $this->kelas_id);
                    })
            )
            ->columns([
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('siswa.nis')
                    ->label('NIS')
                    ->searchable(),
                TextColumn::make('tanggal_presensi')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Izin' => 'info',
                        'Sakit' => 'warning',
                        'Tanpa Keterangan' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('pertemuan_ke')
                    ->label('Pertemuan')
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(Kelas::pluck('nama_kelas', 'id'))
                    ->attribute('kelas_id'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Tanpa Keterangan' => 'Tanpa Keterangan',
                    ]),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('tanggal_mulai')
                            ->label('Dari Tanggal')
                            ->default($this->tanggal_mulai),
                        DatePicker::make('tanggal_selesai')
                            ->label('Sampai Tanggal')
                            ->default($this->tanggal_selesai),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_mulai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_presensi', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_selesai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_presensi', '<=', $date),
                            );
                    }),
            ])
            ->groups([
                Group::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
                Group::make('tanggal_presensi')
                    ->label('Tanggal')
                    ->collapsible(),
                Group::make('status')
                    ->label('Status')
                    ->collapsible(),
            ])
            ->defaultGroup('kelas.nama_kelas')
            ->paginated([10, 25, 50, 100])
            ->headerActions([
                // Pisahkan actions untuk menghindari konflik
                TableAction::make('refreshData')
                    ->label('Refresh Data')
                    ->color('secondary')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        $this->resetTable();
                        $this->dispatch('$refresh');

                        Notification::make()
                            ->title('Data berhasil di-refresh')
                            ->success()
                            ->send();
                    }),

                // Export Excel - Pisah dari ActionGroup
                TableAction::make('exportToExcel')
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $params = [
                            'tanggal_mulai' => $this->tanggal_mulai,
                            'tanggal_selesai' => $this->tanggal_selesai,
                        ];

                        if ($this->kelas_id) {
                            $params['kelas_id'] = $this->kelas_id;
                        }

                        // Tambahkan notification
                        Notification::make()
                            ->title('Sedang memproses export Excel...')
                            ->info()
                            ->send();

                        // Redirect dengan timeout
                        $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.kepala-sekolah', $params) . '"; }, 1000);');
                    }),

                // Export PDF - Pisah dari ActionGroup
                TableAction::make('exportToPdf')
                    ->label('Export PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        $params = [
                            'tanggal_mulai' => $this->tanggal_mulai,
                            'tanggal_selesai' => $this->tanggal_selesai,
                        ];

                        if ($this->kelas_id) {
                            $params['kelas_id'] = $this->kelas_id;
                        }

                        // Tambahkan notification
                        Notification::make()
                            ->title('Sedang memproses export PDF...')
                            ->info()
                            ->send();

                        // Redirect dengan timeout
                        $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.kepala-sekolah-pdf', $params) . '"; }, 1000);');
                    }),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardKepalaSekolahStats::class,
        ];
    }

    public function getKelasStats()
    {
        return Kelas::withCount([
            'siswa as total_siswa',
            'presensi as total_hadir' => function ($query) {
                $query->where('status', 'Hadir')
                    ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);
            },
            'presensi as total_izin' => function ($query) {
                $query->where('status', 'Izin')
                    ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);
            },
            'presensi as total_sakit' => function ($query) {
                $query->where('status', 'Sakit')
                    ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);
            },
            'presensi as total_alpha' => function ($query) {
                $query->where('status', 'Tanpa Keterangan')
                    ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);
            }
        ])->get();
    }

    // Method to update date filters
    public function updateDateFilters($tanggal_mulai, $tanggal_selesai, $kelas_id = null)
    {
        $this->tanggal_mulai = $tanggal_mulai;
        $this->tanggal_selesai = $tanggal_selesai;
        $this->kelas_id = $kelas_id;

        // Refresh the table
        $this->resetTable();
    }

    // Method untuk mendapatkan statistik keseluruhan
    public function getOverallStats()
    {
        $query = Presensi::whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        return [
            'total_data' => $query->count(),
            'total_hadir' => $query->clone()->where('status', 'Hadir')->count(),
            'total_izin' => $query->clone()->where('status', 'Izin')->count(),
            'total_sakit' => $query->clone()->where('status', 'Sakit')->count(),
            'total_alpha' => $query->clone()->where('status', 'Tanpa Keterangan')->count(),
            'total_siswa' => $query->clone()->distinct('siswa_id')->count(),
        ];
    }
}
