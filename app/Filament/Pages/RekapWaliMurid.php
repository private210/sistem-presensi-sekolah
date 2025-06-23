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
use Filament\Forms\Components\Radio;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapWaliKelasExport;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class RekapWaliMurid extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Rekap Presensi Siswa';
    protected static ?string $navigationTitle = 'Rekap Wali Murid';
    protected static ?string $navigationGroup = 'Wali Murid';
    protected static string $view = 'filament.pages.rekap-wali-murid';
    protected static ?int $navigationSort = 1;

    public $tanggal_mulai;
    public $tanggal_selesai;
    public $kelas_id;
    public $periode_type = 'semester'; // Default ke semester
    public $selected_semester;

    public function mount(): void
    {
        // Check user role access
        if (!auth()->user()->hasAnyRole(['Wali Murid', 'super_admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        // Set default periode
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
                Grid::make(3)
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
                    ]),
            ]);
    }

    // Create a separate method for filter form schema
    protected function getFilterFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    Radio::make('periode_type')
                        ->label('Periode')
                        ->options([
                            'bulan' => 'Per Bulan',
                            'semester' => 'Per Semester',
                        ])
                        ->default($this->periode_type)
                        ->reactive(),
                    DatePicker::make('tanggal_mulai')
                        ->label('Dari Tanggal')
                        ->required()
                        ->default($this->tanggal_mulai)
                        ->visible(fn($get) => $get('periode_type') === 'bulan'),
                    DatePicker::make('tanggal_selesai')
                        ->label('Sampai Tanggal')
                        ->required()
                        ->default($this->tanggal_selesai)
                        ->visible(fn($get) => $get('periode_type') === 'bulan'),
                    Select::make('semester')
                        ->label('Pilih Semester')
                        ->options([
                            'ganjil' => 'Semester Ganjil (Juli - Desember)',
                            'genap' => 'Semester Genap (Januari - Juni)',
                        ])
                        ->default($this->selected_semester)
                        ->visible(fn($get) => $get('periode_type') === 'semester'),
                    Select::make('kelas_id')
                        ->label('Kelas')
                        ->options(function () {
                            if (auth()->user()->hasRole('Wali Murid')) {
                                $waliMurid = auth()->user()->waliMurid;
                                if ($waliMurid && $waliMurid->siswa) {
                                    return Kelas::where('id', $waliMurid->siswa->kelas_id)
                                        ->pluck('nama_kelas', 'id');
                                }
                                return collect();
                            }

                            return Kelas::pluck('nama_kelas', 'id');
                        })
                        ->searchable()
                        ->default($this->kelas_id)
                        ->disabled(fn() => auth()->user()->hasRole(['Wali Murid'])),
                ]),
        ];
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
                    ->toggleable(isToggledHiddenByDefault: auth()->user()->hasRole(['Wali Murid'])),
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
                    ->summarize(Count::make()->label('Total')),
                TextColumn::make('pertemuan_ke')
                    ->label('Hari Ke')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Alpa' => 'Alpa (Tanpa Keterangan)',
                    ]),
                SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(function () {
                        if (auth()->user()->hasRole('Wali Murid')) {
                            $waliMurid = auth()->user()->waliMurid;
                            if ($waliMurid && $waliMurid->siswa) {
                                return Kelas::where('id', $waliMurid->siswa->kelas_id)
                                    ->pluck('nama_kelas', 'id');
                            }
                            return collect();
                        }

                        return Kelas::pluck('nama_kelas', 'id');
                    })
                    ->visible(fn() => !auth()->user()->hasRole(['Wali Murid'])),
            ])
            ->groups([
                Group::make('pertemuan_ke')
                    ->label('Hari Ke')
                    ->column('pertemuan_ke')
                    ->collapsible(),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('refreshData')
                ->label('Refresh Data')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->resetTable();
                    $this->dispatch('$refresh');

                    Notification::make()
                        ->title('Data berhasil di-refresh')
                        ->success()
                        ->send();
                }),
                \Filament\Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
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

                        $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.wali-murid', $params) . '"; }, 1000);');
                    }),
                \Filament\Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->color('danger')
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
                            ->title('Sedang memproses export PDF...')
                            ->info()
                            ->send();

                        $this->js('setTimeout(function() { window.location.href = "' . route('export.presensi.wali-murid-pdf', $params) . '"; }, 1000);');
                    }),
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
                        Section::make('Informasi Sistem')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat pada')
                                    ->dateTime('d F Y, H:i:s'),
                                TextEntry::make('updated_at')
                                    ->label('Terakhir diubah')
                                    ->dateTime('d F Y, H:i:s'),
                            ])
                            ->columns(2)
                            ->collapsible(),
                    ])
                    ->modalWidth('2xl')
                    ->color('info')
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                // BulkAction::make('export_selected')
                //     ->label('Export Terpilih')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->action(function (Collection $records) {
                //         return $this->exportSelectedToExcel($records);
                //     }),
            ])
            ->paginated([10, 25, 50, 100]);
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

        return $query;
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

        return Excel::download(
            new RekapWaliKelasExport($data, $this->tanggal_mulai, $this->tanggal_selesai, null, null, null, $this->periode_type),
            'rekap-presensi-' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx'
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

        // Implementation for PDF export using DomPDF or similar
        $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
            'data' => $data,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'kelas' => $this->kelas_id ? Kelas::find($this->kelas_id) : null,
            'periode_type' => $this->periode_type,
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'rekap-presensi-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf'
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

    // Method untuk mendapatkan ringkasan statistik
    public function getRingkasanStats()
    {
        $query = $this->getTableQuery();

        return [
            'total_kehadiran' => $query->clone()->where('status', 'Hadir')->count(),
            'total_izin' => $query->clone()->where('status', 'Izin')->count(),
            'total_sakit' => $query->clone()->where('status', 'Sakit')->count(),
            'total_alpha' => $query->clone()->where('status', 'Alpa')->count(),
            'total_siswa' => $query->clone()->distinct('siswa_id')->count(),
            'persentase_kehadiran' => $this->hitungPersentaseKehadiran(),
        ];
    }

    private function hitungPersentaseKehadiran()
    {
        $query = $this->getTableQuery();
        $totalPresensi = $query->count();
        $totalHadir = $query->clone()->where('status', 'Hadir')->count();

        return $totalPresensi > 0 ? round(($totalHadir / $totalPresensi) * 100, 2) : 0;
    }
}
