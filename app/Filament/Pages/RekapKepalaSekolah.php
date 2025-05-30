<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardKepalaSekolahStats;
use App\Models\Kelas;
use App\Models\Presensi;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
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
                // Use TableAction for table header actions
                TableAction::make('export')
                    ->label('Ekspor Data')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn() => route('export.presensi.kepala-sekolah', [
                        'tanggal_mulai' => $this->tanggal_mulai,
                        'tanggal_selesai' => $this->tanggal_selesai,
                        'kelas_id' => $this->kelas_id,
                    ])),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardKepalaSekolahStats::class,
        ];
    }

    // Page-level header actions (different from table header actions)
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportAllData')
                ->label('Ekspor Semua Data')
                ->color('primary')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    return redirect()->to(route('export.presensi.kepala-sekolah', [
                        'tanggal_mulai' => $this->tanggal_mulai,
                        'tanggal_selesai' => $this->tanggal_selesai,
                        'kelas_id' => $this->kelas_id,
                    ]));
                }),

            Action::make('refreshData')
                ->label('Refresh Data')
                ->color('gray')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->resetTable();
                    $this->dispatch('$refresh');
                }),
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
}
