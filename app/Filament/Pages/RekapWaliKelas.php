<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Presensi;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapWaliKelasExport;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class RekapWaliKelas extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Rekap Wali Kelas';
    protected static ?string $navigationTitle = 'Rekap Presensi';
    protected static ?string $navigationGroup = 'Rekap Presensi Kelas';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.rekap-wali-kelas';

    public $tanggal_mulai;
    public $tanggal_selesai;
    public $kelas_id;
    public $pertemuan_ke = null;
    public $periode_type = 'semester'; // Default ke semester
    public $selected_semester;

    public function mount(): void
    {
        // Check user role access
        if (!auth()->user()->hasAnyRole(['Wali Kelas', 'Kepala Sekolah', 'super_admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        // Set default periode
        $this->setPeriodeDefaults();

        // Set default kelas for wali kelas
        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $this->kelas_id = $waliKelas->kelas_id;
            }
        }

        $this->pertemuan_ke = null;
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
            $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');
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
                            }),
                        DatePicker::make('tanggal_mulai')
                            ->label('Dari Tanggal')
                            ->required()
                            ->default($this->tanggal_mulai)
                            ->reactive()
                            ->visible(fn($get) => $get('periode_type') === 'bulan')
                            ->afterStateUpdated(fn($state) => $this->tanggal_mulai = $state),
                        DatePicker::make('tanggal_selesai')
                            ->label('Sampai Tanggal')
                            ->required()
                            ->default($this->tanggal_selesai)
                            ->reactive()
                            ->visible(fn($get) => $get('periode_type') === 'bulan')
                            ->afterStateUpdated(fn($state) => $this->tanggal_selesai = $state),
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
                            ->searchable()
                            ->default($this->kelas_id)
                            ->reactive()
                            ->afterStateUpdated(function ($state) {
                                $this->kelas_id = $state;
                            })
                            ->disabled(fn() => auth()->user()->hasRole(['Wali Kelas'])),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: auth()->user()->hasRole(['Wali Kelas', 'Wali Murid'])),
                TextColumn::make('siswa.nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('siswa.nama_lengkap')
                    ->label('Nama Siswa')
                    ->sortable()
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
                        'Alpa' => 'danger',
                        default => 'gray',
                    })
                    ->summarize([
                        Count::make()
                            ->label('Total Presensi')
                            ->using(fn($query) => $query->count())
                    ]),
                TextColumn::make('pertemuan_ke')
                    ->label('Hari')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
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
                                    ->label('Hari Ke'),
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
                        'Alpa' => 'Alpa',
                    ]),
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
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
                    ->visible(fn() => !auth()->user()->hasRole(['Wali Kelas'])),
            ])
            ->groups([
                Group::make('pertemuan_ke')
                    ->label('Hari Ke')
                    ->collapsible(),
                Group::make('status')
                    ->label('Status')
                    ->collapsible(),
            ])
            ->defaultGroup('pertemuan_ke')
            ->headerActions([
                \Filament\Tables\Actions\Action::make('refreshData')
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
                \Filament\Tables\Actions\Action::make('exportToExcel')
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

                        $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.wali-kelas', $params) . '"; }, 1000);');
                    }),

                \Filament\Tables\Actions\Action::make('exportToPdf')
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

                        $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.wali-kelas-pdf', $params) . '"; }, 1000);');
                    }),
                \Filament\Tables\Actions\Action::make('input_presensi')
                    ->label('Input Presensi Hari Ini')
                    ->color('primary')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->action(function () {
                        return redirect()->to(route('filament.admin.pages.presensi-kelas'));
                    }),
            ])
            ->bulkActions([
                // BulkAction::make('export_selected')
                //     ->label('Export Terpilih')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->action(function (Collection $records) {
                //         return $this->exportSelectedToExcel($records);
                //     }),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    protected function getTableQuery(): Builder
    {
        $query = Presensi::query()
            ->with(['siswa', 'kelas'])
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);

        // Filter berdasarkan role
        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            }
        } elseif (auth()->user()->hasRole('Wali Murid')) {
            $waliMurid = auth()->user()->waliMurid;
            if ($waliMurid) {
                $query->where('siswa_id', $waliMurid->siswa_id);
            }
        }

        // Filter kelas jika dipilih
        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        return $query->orderBy('pertemuan_ke')->orderBy('siswa_id')->orderBy('tanggal_presensi', 'desc');
    }

    protected function getLatestPertemuan(): ?int
    {
        $query = Presensi::query()
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            }
        }

        return $query->max('pertemuan_ke');
    }

    protected function getAvailablePertemuan(): array
    {
        $query = Presensi::query()
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            }
        }

        $pertemuanList = $query->distinct('pertemuan_ke')
            ->orderBy('pertemuan_ke')
            ->pluck('pertemuan_ke')
            ->filter()
            ->toArray();

        $options = [];
        foreach ($pertemuanList as $pertemuan) {
            $options[$pertemuan] = "Pertemuan ke-{$pertemuan}";
        }

        return $options;
    }

    protected function shouldShowLatestByDefault(): bool
    {
        return auth()->user()->hasRole('Wali Kelas');
    }

    public function getSiswaTidakHadir($pertemuan = null): Collection
    {
        $pertemuan = $pertemuan ?? $this->getLatestPertemuan();

        if (!$pertemuan || !$this->kelas_id) {
            return collect();
        }

        $allSiswa = Siswa::where('kelas_id', $this->kelas_id)->get();

        $siswaHadir = Presensi::where('kelas_id', $this->kelas_id)
            ->where('pertemuan_ke', $pertemuan)
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai])
            ->pluck('siswa_id')
            ->toArray();

        return $allSiswa->whereNotIn('id', $siswaHadir);
    }

    public function getRingkasanPerPertemuan(): array
    {
        $query = $this->getTableQuery();

        $ringkasan = $query->selectRaw('
            pertemuan_ke,
            COUNT(*) as total_presensi,
            SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as total_hadir,
            SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as total_izin,
            SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as total_sakit,
            SUM(CASE WHEN status = "Alpa" THEN 1 ELSE 0 END) as total_alpha
        ')
            ->groupBy('pertemuan_ke')
            ->orderBy('pertemuan_ke')
            ->get()
            ->toArray();

        return $ringkasan;
    }

    public function exportToExcel()
    {
        $data = $this->getTableQuery()->get();

        if ($data->isEmpty()) {
            Notification::make()
                ->title('Tidak ada data untuk diekspor')
                ->warning()
                ->send();
            return;
        }

        $filename = 'rekap-presensi-' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx';

        return Excel::download(
            new RekapWaliKelasExport($data, $this->tanggal_mulai, $this->tanggal_selesai, null, null, null, $this->periode_type),
            $filename
        );
    }

    public function exportToPdf()
    {
        $data = $this->getTableQuery()->get();

        if ($data->isEmpty()) {
            Notification::make()
                ->title('Tidak ada data untuk diekspor')
                ->warning()
                ->send();
            return;
        }

        $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
            'data' => $data,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'kelas' => $this->kelas_id ? Kelas::find($this->kelas_id) : null,
            'periode_type' => $this->periode_type,
        ]);

        $filename = 'rekap-presensi-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $filename
        );
    }

    public function exportSelectedToExcel(Collection $records)
    {
        if ($records->isEmpty()) {
            Notification::make()
                ->title('Tidak ada data yang dipilih')
                ->warning()
                ->send();
            return;
        }

        return Excel::download(
            new RekapWaliKelasExport($records, $this->tanggal_mulai, $this->tanggal_selesai, null, null, null, $this->periode_type),
            'rekap-presensi-selected-' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx'
        );
    }

    public function getRingkasanStats()
    {
        $query = $this->getTableQuery();

        $stats = [
            'total_kehadiran' => $query->clone()->where('status', 'Hadir')->count(),
            'total_izin' => $query->clone()->where('status', 'Izin')->count(),
            'total_sakit' => $query->clone()->where('status', 'Sakit')->count(),
            'total_alpha' => $query->clone()->where('status', 'Alpa')->count(),
            'total_siswa' => $query->clone()->distinct('siswa_id')->count(),
            'persentase_kehadiran' => $this->hitungPersentaseKehadiran(),
            'total_pertemuan' => count($this->getAvailablePertemuan()),
        ];

        return $stats;
    }

    private function hitungPersentaseKehadiran()
    {
        $query = $this->getTableQuery();
        $totalPresensi = $query->count();
        $totalHadir = $query->clone()->where('status', 'Hadir')->count();

        return $totalPresensi > 0 ? round(($totalHadir / $totalPresensi) * 100, 2) : 0;
    }

    public function updatedTanggalMulai()
    {
        // Refresh table when date changes
    }

    public function updatedTanggalSelesai()
    {
        // Refresh table when date changes
    }

    public function updatedKelasId()
    {
        // Refresh table when class changes
    }
    public function updatedPeriodeType()
    {
        $this->setPeriodeDefaults();
        // Refresh table when periode type changes
    }

    public function updatedSemester()
    {
        $this->setPeriodeDefaults();
        // Refresh table when semester changes
    }
}
