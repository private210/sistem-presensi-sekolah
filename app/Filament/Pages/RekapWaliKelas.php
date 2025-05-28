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
    protected static ?string $navigationGroup = 'Wali Kelas';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.rekap-wali-kelas';

    public $tanggal_mulai;
    public $tanggal_selesai;
    public $kelas_id;
    public $pertemuan_ke = null; // Tambahan untuk filter pertemuan

    public function mount(): void
    {
        // Check user role access
        if (!auth()->user()->hasAnyRole(['Wali Kelas', 'Kepala Sekolah', 'super_admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Set default kelas for wali kelas
        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $this->kelas_id = $waliKelas->kelas_id;
            }
        }

        // Tidak set pertemuan default, tampilkan semua data
        $this->pertemuan_ke = null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3) // Ubah kembali ke 3 karena menghapus field pertemuan
                    ->schema([
                        DatePicker::make('tanggal_mulai')
                            ->label('Dari Tanggal')
                            ->required()
                            ->default($this->tanggal_mulai)
                            ->reactive()
                            ->afterStateUpdated(fn($state) => $this->tanggal_mulai = $state),
                        DatePicker::make('tanggal_selesai')
                            ->label('Sampai Tanggal')
                            ->required()
                            ->default($this->tanggal_selesai)
                            ->reactive()
                            ->afterStateUpdated(fn($state) => $this->tanggal_selesai = $state),
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
                        'Tanpa Keterangan' => 'danger',
                        default => 'gray',
                    })
                    ->summarize([
                        Count::make()
                            ->label('Total Pesensi')
                            ->using(fn($query) => $query->count())
                    ]),
                TextColumn::make('pertemuan_ke')
                    ->label('Pertemuan')
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
                                    ->label('Pertemuan')
                                    ->formatStateUsing(fn($state) => 'Pertemuan ke-' . $state),
                                TextEntry::make('status')
                                    ->label('Status Kehadiran')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'Hadir' => 'success',
                                        'Izin' => 'info',
                                        'Sakit' => 'warning',
                                        'Tanpa Keterangan' => 'danger',
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
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'Tanpa Keterangan' => 'Tanpa Keterangan',
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
                    ->label('Pertemuan')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn($record) => 'Pertemuan ke-' . $record->pertemuan_ke),
                Group::make('siswa.nama_lengkap')
                    ->label('Siswa')
                    ->collapsible(),
                Group::make('kelas.nama_kelas')
                    ->label('Kelas')
                    ->collapsible(),
                Group::make('status')
                    ->label('Status')
                    ->collapsible(),
            ])
            ->defaultGroup('pertemuan_ke') // Default grouping berdasarkan pertemuan
            ->headerActions([
                \Filament\Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return $this->exportToExcel();
                    }),
                \Filament\Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        return $this->exportToPdf();
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
                BulkAction::make('export_selected')
                    ->label('Export Terpilih')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        return $this->exportSelectedToExcel($records);
                    }),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25); // Increase default pagination
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

        // Tampilkan semua data pertemuan tanpa filter
        return $query->orderBy('pertemuan_ke')->orderBy('siswa_id')->orderBy('tanggal_presensi', 'desc');
    }

    /**
     * Mendapatkan nomor pertemuan terbaru
     */
    protected function getLatestPertemuan(): ?int
    {
        $query = Presensi::query()
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        // Filter berdasarkan role
        if (auth()->user()->hasRole('Wali Kelas')) {
            $waliKelas = auth()->user()->waliKelas;
            if ($waliKelas) {
                $query->where('kelas_id', $waliKelas->kelas_id);
            }
        }

        return $query->max('pertemuan_ke');
    }

    /**
     * Mendapatkan daftar pertemuan yang tersedia
     */
    protected function getAvailablePertemuan(): array
    {
        $query = Presensi::query()
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai]);

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        // Filter berdasarkan role
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

    /**
     * Menentukan apakah harus menampilkan pertemuan terbaru secara default
     */
    protected function shouldShowLatestByDefault(): bool
    {
        // Tampilkan pertemuan terbaru jika user adalah wali kelas
        return auth()->user()->hasRole('Wali Kelas');
    }

    /**
     * Mendapatkan daftar siswa yang tidak hadir pada pertemuan tertentu
     */
    public function getSiswaTidakHadir($pertemuan = null): Collection
    {
        $pertemuan = $pertemuan ?? $this->getLatestPertemuan();

        if (!$pertemuan || !$this->kelas_id) {
            return collect();
        }

        // Dapatkan semua siswa di kelas
        $allSiswa = Siswa::where('kelas_id', $this->kelas_id)->get();

        // Dapatkan siswa yang sudah presensi pada pertemuan ini
        $siswaHadir = Presensi::where('kelas_id', $this->kelas_id)
            ->where('pertemuan_ke', $pertemuan)
            ->whereBetween('tanggal_presensi', [$this->tanggal_mulai, $this->tanggal_selesai])
            ->pluck('siswa_id')
            ->toArray();

        // Return siswa yang belum presensi
        return $allSiswa->whereNotIn('id', $siswaHadir);
    }

    /**
     * Mendapatkan ringkasan per pertemuan
     */
    public function getRingkasanPerPertemuan(): array
    {
        $query = $this->getTableQuery();

        $ringkasan = $query->selectRaw('
            pertemuan_ke,
            COUNT(*) as total_presensi,
            SUM(CASE WHEN status = "Hadir" THEN 1 ELSE 0 END) as total_hadir,
            SUM(CASE WHEN status = "Izin" THEN 1 ELSE 0 END) as total_izin,
            SUM(CASE WHEN status = "Sakit" THEN 1 ELSE 0 END) as total_sakit,
            SUM(CASE WHEN status = "Tanpa Keterangan" THEN 1 ELSE 0 END) as total_alpha
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
            new RekapWaliKelasExport($data),
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

        // Implementation for PDF export using DomPDF or similar
        $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
            'data' => $data,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'kelas' => $this->kelas_id ? Kelas::find($this->kelas_id) : null,
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
            new RekapWaliKelasExport($records),
            'rekap-presensi-selected-' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx'
        );
    }

    // Method untuk mendapatkan ringkasan statistik
    public function getRingkasanStats()
    {
        $query = $this->getTableQuery();

        $stats = [
            'total_kehadiran' => $query->clone()->where('status', 'Hadir')->count(),
            'total_izin' => $query->clone()->where('status', 'Izin')->count(),
            'total_sakit' => $query->clone()->where('status', 'Sakit')->count(),
            'total_alpha' => $query->clone()->where('status', 'Tanpa Keterangan')->count(),
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

    /**
     * Method untuk refresh data ketika form berubah
     */
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
}
