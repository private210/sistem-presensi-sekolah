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
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Session;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class RekapKepalaSekolah extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;
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
    public $periode_type = 'semester'; // Default ke semester
    public $selected_semester = 'current'; // Current semester by default

    public function mount(): void
    {
        if (!auth()->user()->hasRole('Kepala Sekolah') && !auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        // Set default berdasarkan periode type
        $this->setPeriodeDefaults();
    }

    protected function setPeriodeDefaults(): void
    {
        if ($this->periode_type === 'semester') {
            // Tentukan semester saat ini
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            if ($currentMonth >= 7 && $currentMonth <= 12) {
                // Semester Ganjil (Juli - Desember)
                $this->tanggal_mulai = Carbon::create($currentYear, 7, 1)->format('Y-m-d');
                $this->tanggal_selesai = Carbon::create($currentYear, 12, 31)->format('Y-m-d');
                $this->selected_semester = 'ganjil';
            } else {
                // Semester Genap (Januari - Juni)
                $this->tanggal_mulai = Carbon::create($currentYear, 1, 1)->format('Y-m-d');
                $this->tanggal_selesai = Carbon::create($currentYear, 6, 30)->format('Y-m-d');
                $this->selected_semester = 'genap';
            }
        } else {
            // Default untuk bulan
            $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
            $this->tanggal_selesai = Carbon::now()->format('Y-m-d');
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
                    ->schema([
                        Radio::make('periode_type')
                            ->label('Periode')
                            ->options([
                                'bulan' => 'Per Bulan',
                                'semester' => 'Per Semester',
                            ])
                            ->default($this->periode_type)
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->periode_type = $state;
                                $this->setPeriodeDefaults();
                                $this->resetTable();
                                $this->dispatch('form-updated');
                            }),
                        DatePicker::make('tanggal_mulai')
                            ->label('Dari Tanggal')
                            ->required()
                            ->default($this->tanggal_mulai)
                            ->reactive()
                            ->visible(fn($get) => $get('periode_type') === 'bulan')
                            ->afterStateUpdated(function ($state) {
                                $this->tanggal_mulai = $state;
                                $this->dispatch('form-updated');
                            }),
                        DatePicker::make('tanggal_selesai')
                            ->label('Sampai Tanggal')
                            ->required()
                            ->default($this->tanggal_selesai)
                            ->reactive()
                            ->visible(fn($get) => $get('periode_type') === 'bulan')
                            ->afterStateUpdated(function ($state) {
                                $this->tanggal_selesai = $state;
                                $this->dispatch('form-updated');
                            }),
                        Select::make('semester')
                            ->label('Pilih Semester')
                            ->options([
                                'ganjil' => 'Semester Ganjil (Juli - Desember)',
                                'genap' => 'Semester Genap (Januari - Juni)',
                            ])
                            ->default($this->selected_semester)
                            ->visible(fn($get) => $get('periode_type') === 'semester')
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->selected_semester = $state;
                                $currentYear = Carbon::now()->year;
                                if ($state === 'ganjil') {
                                    $this->tanggal_mulai = Carbon::create($currentYear, 7, 1)->format('Y-m-d');
                                    $this->tanggal_selesai = Carbon::create($currentYear, 12, 31)->format('Y-m-d');
                                } else {
                                    $this->tanggal_mulai = Carbon::create($currentYear, 1, 1)->format('Y-m-d');
                                    $this->tanggal_selesai = Carbon::create($currentYear, 6, 30)->format('Y-m-d');
                                }
                                $this->resetTable();
                                $this->dispatch('form-updated');
                            }),
                        Select::make('kelas_id')
                            ->label('Kelas')
                            ->placeholder('Semua Kelas')
                            ->options(['' => 'Semua Kelas'] + Kelas::pluck('nama_kelas', 'id')->toArray())
                            ->searchable()
                            ->default($this->kelas_id)
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->kelas_id = $state;
                                $this->resetTable();
                                $this->dispatch('form-updated');
                            }),
                    ]),
            ]);
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
                        'Tanpa Keterangan', 'Alpa' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('pertemuan_ke')
                    ->label('Hari Ke')
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Detail')
                    ->modalHeading(fn($record) => 'Detail Presensi - ' . $record->siswa->nama_lengkap)
                    ->infolist([
                        Section::make('Informasi Presensi')
                            ->schema([
                                TextEntry::make('tanggal_presensi')
                                    ->label('Tanggal Presensi')
                                    ->date('d F Y'),
                                TextEntry::make('pertemuan_ke')
                                    ->label('Pertemuan')
                                    ->formatStateUsing(fn($state) => 'Pertemuan ke-' . $state),
                                TextEntry::make('status')
                                    ->label('Status Kehadiran')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'Hadir' => 'success',
                                        'Izin' => 'info',
                                        'Sakit' => 'warning',
                                        'Alpa' => 'danger',
                                        default => 'gray',
                                    }),
                            ])
                            ->columns(3),
                        Section::make('Data Siswa')
                            ->schema([
                                TextEntry::make('siswa.nis')
                                    ->label('NIS'),
                                TextEntry::make('siswa.nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->weight('bold'),
                                TextEntry::make('kelas.nama_kelas')
                                    ->label('Kelas'),
                                TextEntry::make('siswa.jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->placeholder('Tidak diisi'),
                                TextEntry::make('siswa.tempat_lahir')
                                    ->label('Tempat Lahir')
                                    ->placeholder('Tidak diisi'),
                                TextEntry::make('siswa.tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->date('d F Y')
                                    ->placeholder('Tidak diisi'),
                            ])
                            ->columns(2),
                        Section::make('Keterangan')
                            ->schema([
                                TextEntry::make('keterangan')
                                    ->label('Keterangan')
                                    ->placeholder('Tidak ada keterangan')
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn($record) => !empty($record->keterangan)),
                    ])
                    ->modalWidth('2xl')
                    ->color('info')
                    ->icon('heroicon-o-eye'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Tanpa Keterangan' => 'Tanpa Keterangan',
                        'Alpa' => 'Alpa',
                    ]),
            ])
            ->groups([
                Group::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
                Group::make('tanggal_presensi')
                    ->label('Tanggal')
                    ->collapsible(),
                Group::make('pertemuan_ke')
                    ->label('Hari Ke')
                    ->collapsible(),
            ])
            ->defaultGroup('kelas.nama_kelas')
            ->paginated([10, 25, 50, 100])
            ->headerActions([
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
                TableAction::make('exportToExcel')
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $params = [
                            'tanggal_mulai' => $this->tanggal_mulai,
                            'tanggal_selesai' => $this->tanggal_selesai,
                            'periode_type' => $this->periode_type,
                        ];

                        if ($this->kelas_id) {
                            $params['kelas_id'] = $this->kelas_id;
                        }

                        Notification::make()
                            ->title('Sedang memproses export Excel...')
                            ->info()
                            ->send();

                        $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.kepala-sekolah', $params) . '"; }, 1000);');
                    }),
                TableAction::make('exportToPdf')
                    ->label('Export PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-text')
                    ->action(function () {
                        $params = [
                            'tanggal_mulai' => $this->tanggal_mulai,
                            'tanggal_selesai' => $this->tanggal_selesai,
                            'periode_type' => $this->periode_type,
                        ];

                        if ($this->kelas_id) {
                            $params['kelas_id'] = $this->kelas_id;
                        }

                        Notification::make()
                            ->title('Sedang memproses export PDF...')
                            ->info()
                            ->send();

                        $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.kepala-sekolah-pdf', $params) . '"; }, 1000);');
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkExportExcel')
                        ->label('Export Excel (Data Terpilih)')
                        ->color('success')
                        ->icon('heroicon-o-document-arrow-down')
                        ->requiresConfirmation()
                        ->modalHeading('Export Data Terpilih ke Excel')
                        ->modalDescription('Apakah Anda yakin ingin mengexport data presensi yang dipilih ke Excel?')
                        ->modalSubmitActionLabel('Ya, Export')
                        ->action(function (Collection $records) {
                            // Simpan ID records yang dipilih ke session
                            $recordIds = $records->pluck('id')->toArray();
                            Session::put('selected_presensi_ids', $recordIds);

                            Notification::make()
                                ->title('Sedang memproses export Excel...')
                                ->body('Total ' . count($recordIds) . ' data akan diexport.')
                                ->info()
                                ->send();

                            // Redirect ke route export dengan parameter bulk
                            $params = [
                                'tanggal_mulai' => $this->tanggal_mulai,
                                'tanggal_selesai' => $this->tanggal_selesai,
                                'periode_type' => $this->periode_type,
                                'bulk_export' => true,
                            ];

                            if ($this->kelas_id) {
                                $params['kelas_id'] = $this->kelas_id;
                            }

                            $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.kepala-sekolah', $params) . '"; }, 1000);');
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulkExportPdf')
                        ->label('Export PDF (Data Terpilih)')
                        ->color('danger')
                        ->icon('heroicon-o-document-text')
                        ->requiresConfirmation()
                        ->modalHeading('Export Data Terpilih ke PDF')
                        ->modalDescription('Apakah Anda yakin ingin mengexport data presensi yang dipilih ke PDF?')
                        ->modalSubmitActionLabel('Ya, Export')
                        ->action(function (Collection $records) {
                            // Simpan ID records yang dipilih ke session
                            $recordIds = $records->pluck('id')->toArray();
                            Session::put('selected_presensi_ids', $recordIds);

                            Notification::make()
                                ->title('Sedang memproses export PDF...')
                                ->body('Total ' . count($recordIds) . ' data akan diexport.')
                                ->info()
                                ->send();

                            // Redirect ke route export dengan parameter bulk
                            $params = [
                                'tanggal_mulai' => $this->tanggal_mulai,
                                'tanggal_selesai' => $this->tanggal_selesai,
                                'periode_type' => $this->periode_type,
                                'bulk_export' => true,
                            ];

                            if ($this->kelas_id) {
                                $params['kelas_id'] = $this->kelas_id;
                            }

                            $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.kepala-sekolah-pdf', $params) . '"; }, 1000);');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public function getKelasStats()
    {
        $query = Kelas::query();

        // Jika ada filter kelas, hanya ambil kelas tersebut
        if ($this->kelas_id) {
            $query->where('id', $this->kelas_id);
        }

        return $query->withCount([
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
                $query->whereIn('status', ['Tanpa Keterangan', 'Alpa'])
                    ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);
            }
        ])->get();
    }

    public function updateDateFilters($tanggal_mulai, $tanggal_selesai, $kelas_id = null)
    {
        $this->tanggal_mulai = $tanggal_mulai;
        $this->tanggal_selesai = $tanggal_selesai;
        $this->kelas_id = $kelas_id;

        $this->resetTable();
    }

    public function getOverallStats()
    {
        $query = Presensi::whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        $totalData = $query->count();
        $totalHadir = $query->clone()->where('status', 'Hadir')->count();
        $totalIzin = $query->clone()->where('status', 'Izin')->count();
        $totalSakit = $query->clone()->where('status', 'Sakit')->count();
        $totalAlpha = $query->clone()->whereIn('status', ['Tanpa Keterangan', 'Alpa'])->count();
        $totalSiswa = $query->clone()->distinct('siswa_id')->count();

        // Hitung persentase
        $hadirPersen = $totalData > 0 ? round(($totalHadir / $totalData) * 100, 2) : 0;
        $izinPersen = $totalData > 0 ? round(($totalIzin / $totalData) * 100, 2) : 0;
        $sakitPersen = $totalData > 0 ? round(($totalSakit / $totalData) * 100, 2) : 0;
        $alphaPersen = $totalData > 0 ? round(($totalAlpha / $totalData) * 100, 2) : 0;

        return [
            'total_data' => $totalData,
            'total_hadir' => $totalHadir,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alpha' => $totalAlpha,
            'total_siswa' => $totalSiswa,
            'hadir_persen' => $hadirPersen,
            'izin_persen' => $izinPersen,
            'sakit_persen' => $sakitPersen,
            'alpha_persen' => $alphaPersen,
        ];
    }

    // Event listeners untuk update reactive
    public function updatedTanggalMulai()
    {
        $this->resetTable();
        $this->dispatch('form-updated');
    }

    public function updatedTanggalSelesai()
    {
        $this->resetTable();
        $this->dispatch('form-updated');
    }

    public function updatedKelasId()
    {
        $this->resetTable();
        $this->dispatch('form-updated');
    }

    public function updatedPeriodeType()
    {
        $this->setPeriodeDefaults();
        $this->resetTable();
        $this->dispatch('form-updated');
    }

    public function updatedSelectedSemester()
    {
        $this->setPeriodeDefaults();
        $this->resetTable();
        $this->dispatch('form-updated');
    }
}
