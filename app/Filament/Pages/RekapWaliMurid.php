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

class RekapWaliMurid extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Rekap Wali Murid';
    protected static ?string $navigationTitle = 'Rekap Wali Murid';
    protected static ?string $navigationGroup = 'Wali Murid';
    protected static string $view = 'filament.pages.rekap-wali-murid';
    protected static ?int $navigationSort = 1;

    public $tanggal_mulai;
    public $tanggal_selesai;
    public $kelas_id;

    public function mount(): void
    {
        // Check user role access
        if (!auth()->user()->hasAnyRole(['Wali Murid', 'super_admin'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->format('Y-m-d');

        // Set default kelas for wali kelas
        // if (auth()->user()->hasRole('Wali Kelas')) {
        //     $waliKelas = auth()->user()->waliKelas;
        //     if ($waliKelas) {
        //         $this->kelas_id = $waliKelas->kelas_id;
        //     }
        // }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)
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
                        // Select::make('kelas_id')
                        //     ->label('Kelas')
                        //     ->options(function () {
                        //         if (auth()->user()->hasRole('Wali Kelas')) {
                        //             $waliKelas = auth()->user()->waliKelas;
                        //             if ($waliKelas) {
                        //                 return Kelas::where('id', $waliKelas->kelas_id)
                        //                     ->pluck('nama_kelas', 'id');
                        //             }
                        //             return collect();
                        //         } elseif (auth()->user()->hasRole('Wali Murid')) {
                        //             $waliMurid = auth()->user()->waliMurid;
                        //             if ($waliMurid && $waliMurid->siswa) {
                        //                 return Kelas::where('id', $waliMurid->siswa->kelas_id)
                        //                     ->pluck('nama_kelas', 'id');
                        //             }
                        //             return collect();
                        //         }

                        //         return Kelas::pluck('nama_kelas', 'id');
                        //     })
                        //     ->searchable()
                        //     ->default($this->kelas_id)
                        //     ->reactive()
                        //     ->afterStateUpdated(fn($state) => $this->kelas_id = $state)
                        //     ->disabled(fn() => auth()->user()->hasRole(['Wali Kelas', 'Wali Murid'])),
                    ]),
            ]);
    }

    // Create a separate method for filter form schema
    protected function getFilterFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    DatePicker::make('tanggal_mulai')
                        ->label('Dari Tanggal')
                        ->required()
                        ->default($this->tanggal_mulai),
                    DatePicker::make('tanggal_selesai')
                        ->label('Sampai Tanggal')
                        ->required()
                        ->default($this->tanggal_selesai),
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
                        ->disabled(fn() => auth()->user()->hasRole([ 'Wali Murid'])),
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
                    ->label('Pertemuan')
                    ->sortable(),
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
                    ->visible(fn() => !auth()->user()->hasRole([ 'Wali Murid'])),
            ])
            ->groups([
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
            // ->defaultGroup(auth()->user()->hasRole('Wali Murid') ? 'tanggal_presensi' : 'siswa.nama_lengkap')
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
            ])
            ->bulkActions([
                BulkAction::make('export_selected')
                    ->label('Export Terpilih')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Collection $records) {
                        return $this->exportSelectedToExcel($records);
                    }),
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
            new RekapWaliKelasExport($data),
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
            new RekapWaliKelasExport($records),
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
