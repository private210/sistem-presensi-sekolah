<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Izin;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Presensi;
use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\HariLibur;
use App\Models\WaliKelas;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class PresensiKelas extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Presensi Kelas';
    protected static ?string $navigationGroup = 'Wali Kelas';
    protected static ?string $title = 'Presensi Kelas Wali Kelas';
    protected static string $view = 'filament.pages.presensi-kelas';

    protected static ?int $navigationSort = 1;

    public $tanggal;
    public $pertemuan_ke = 1;
    public $kelas_id;
    public $kelas_info;
    public $siswa_list = [];
    public $is_holiday = false;
    public $is_weekend = false;
    public $holiday_info = null;
    public $weekend_info = null;
    public $is_non_school_day = false;

    // Tambahkan property untuk menyimpan nama kelas dan jumlah siswa
    public $kelas_nama;
    public $jumlah_siswa;

    // TAMBAHKAN PROPERTY STATUS HARI
    public $status_hari = 'Hari Sekolah';

    public function mount()
    {
        // Cek apakah user adalah wali kelas
        if (!auth()->user()->hasRole('Wali Kelas') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        // Set default tanggal to today
        $this->tanggal = Carbon::today()->format('Y-m-d');

        // Get current Wali Kelas's class
        $waliKelas = auth()->user()->waliKelas;
        if ($waliKelas) {
            $this->kelas_id = $waliKelas->kelas_id;
            $this->kelas_info = Kelas::find($this->kelas_id);

            // Pastikan kelas_info tidak null dan memiliki properti yang dibutuhkan
            if ($this->kelas_info) {
                // Set nilai yang akan ditampilkan di form
                $this->kelas_nama = $this->kelas_info->nama_kelas;

                // Load siswa list
                $this->loadSiswaList();

                // Update jumlah siswa
                $this->jumlah_siswa = count($this->siswa_list);

                // Check if today is a holiday or weekend
                $this->checkNonSchoolDay();

                // Auto set pertemuan_ke
                $this->setPertemuanKe();
            } else {
                Notification::make()
                    ->title('Data kelas tidak ditemukan')
                    ->warning()
                    ->send();
            }
        } else {
            Notification::make()
                ->title('Anda belum ditugaskan sebagai wali kelas')
                ->warning()
                ->send();
        }
    }

    protected function loadSiswaList()
    {
        // Get student list from this class
        $siswa = Siswa::where('kelas_id', $this->kelas_id)
            ->where('is_active', true)
            ->orderBy('nama_lengkap')
            ->get();

        // Reset siswa_list
        $this->siswa_list = [];

        foreach ($siswa as $s) {
            // Check if there's already a presensi record for this date
            $presensi = Presensi::where('siswa_id', $s->id)
                ->where('tanggal_presensi', $this->tanggal)
                ->first();

            // Check if there's an approved izin for this date
            $izin = Izin::where('siswa_id', $s->id)
                ->where('status', 'Disetujui')
                ->where('tanggal_mulai', '<=', $this->tanggal)
                ->where('tanggal_selesai', '>=', $this->tanggal)
                ->first();

            // LOGIC PERUBAHAN: Default status untuk hari libur/weekend
            $status = null; // Ubah dari 'Hadir' menjadi null
            $keterangan = '';
            $has_izin = false;
            $izin_id = null;

            // Set default status dan keterangan berdasarkan jenis hari
            if ($this->is_non_school_day) {
                $status = null; // Tidak ada status untuk hari libur
                if ($this->is_weekend) {
                    $keterangan = 'Hari libur akhir pekan - ' . $this->weekend_info->day_name;
                } elseif ($this->is_holiday) {
                    $keterangan = 'Hari libur resmi - ' . $this->holiday_info->nama_hari_libur;
                }
            } else {
                // Hari sekolah normal, default ke 'Hadir'
                $status = 'Hadir';
            }

            // Set status based on approved izin first (hanya untuk hari sekolah)
            if ($izin && !$this->is_non_school_day) {
                $status = $izin->jenis_izin; // 'Sakit' atau 'Izin'
                $keterangan = $izin->keterangan;
                $has_izin = true;
                $izin_id = $izin->id;
            }

            // Override with presensi data if exists
            if ($presensi) {
                $status = $presensi->status;
                $keterangan = $presensi->keterangan ?? $keterangan;
            }

            $this->siswa_list[] = [
                'siswa_id' => $s->id,
                'nis' => $s->nis,
                'nama_lengkap' => $s->nama_lengkap,
                'status' => $status,
                'keterangan' => $keterangan,
                'presensi_id' => $presensi ? $presensi->id : null,
                'has_izin' => $has_izin,
                'izin_id' => $izin_id,
                'izin_info' => $izin ? [
                    'jenis_izin' => $izin->jenis_izin,
                    'tanggal_mulai' => $izin->tanggal_mulai->format('d/m/Y'),
                    'tanggal_selesai' => $izin->tanggal_selesai->format('d/m/Y'),
                    'keterangan' => $izin->keterangan,
                ] : null,
            ];
        }

        // Update jumlah siswa setelah memuat data
        $this->jumlah_siswa = count($this->siswa_list);
    }

    protected function checkNonSchoolDay()
    {
        $date = Carbon::parse($this->tanggal);

        // Reset all flags
        $this->is_holiday = false;
        $this->is_weekend = false;
        $this->is_non_school_day = false;
        $this->holiday_info = null;
        $this->weekend_info = null;

        // Check if it's weekend (Saturday = 6, Sunday = 0)
        if ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY) {
            $this->is_weekend = true;
            $this->is_non_school_day = true;
            $this->weekend_info = (object)[
                'nama_hari_libur' => 'Hari Libur Akhir Pekan',
                'keterangan' => 'Hari ' . $date->translatedFormat('l') . ' - Tidak ada kegiatan pembelajaran',
                'day_name' => $date->translatedFormat('l')
            ];
        }

        // Check if it's a defined holiday
        $holiday = HariLibur::where('tanggal', $this->tanggal)->first();
        if ($holiday) {
            $this->is_holiday = true;
            $this->is_non_school_day = true;
            $this->holiday_info = $holiday;
        }

        // TAMBAHKAN METHOD UNTUK UPDATE STATUS HARI
        $this->updateStatusHari();
    }

    // TAMBAHKAN METHOD INI
    protected function updateStatusHari()
    {
        if ($this->is_weekend) {
            $this->status_hari = 'Akhir Pekan';
        } elseif ($this->is_holiday) {
            $this->status_hari = 'Hari Libur';
        } else {
            $this->status_hari = 'Hari Sekolah';
        }
    }

    protected function setPertemuanKe()
    {
        // Get the latest pertemuan_ke for this class before the selected date
        $latestPresensi = Presensi::where('kelas_id', $this->kelas_id)
            ->where('tanggal_presensi', '<', $this->tanggal)
            ->orderBy('tanggal_presensi', 'desc')
            ->orderBy('pertemuan_ke', 'desc')
            ->first();

        // Check if there's already presensi for this exact date
        $existingPresensi = Presensi::where('kelas_id', $this->kelas_id)
            ->where('tanggal_presensi', $this->tanggal)
            ->first();

        if ($existingPresensi) {
            // Use existing pertemuan_ke if presensi already exists for this date
            $this->pertemuan_ke = $existingPresensi->pertemuan_ke;
        } elseif ($latestPresensi) {
            // Count valid school days between last presensi and current date
            $lastDate = Carbon::parse($latestPresensi->tanggal_presensi);
            $currentDate = Carbon::parse($this->tanggal);

            $schoolDaysCount = 0;
            $checkDate = $lastDate->copy()->addDay();

            while ($checkDate->lt($currentDate)) {
                // Skip weekends
                if ($checkDate->dayOfWeek !== Carbon::SATURDAY && $checkDate->dayOfWeek !== Carbon::SUNDAY) {
                    // Check if it's not a holiday
                    $holiday = HariLibur::where('tanggal', $checkDate->format('Y-m-d'))->first();
                    if (!$holiday) {
                        $schoolDaysCount++;
                    }
                }
                $checkDate->addDay();
            }

            // Add school days count to last pertemuan_ke, but only if current date is not weekend/holiday
            if (!$this->is_non_school_day) {
                $this->pertemuan_ke = $latestPresensi->pertemuan_ke + $schoolDaysCount + 1;
            } else {
                $this->pertemuan_ke = $latestPresensi->pertemuan_ke;
            }
        } else {
            // First presensi for this class
            $this->pertemuan_ke = $this->is_non_school_day ? 0 : 1;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->required()
                            ->default(now())
                            ->live() // GANTI reactive() dengan live()
                            ->afterStateUpdated(function () {
                                $this->loadSiswaList();
                                $this->checkNonSchoolDay();
                                $this->setPertemuanKe();
                            }),
                        TextInput::make('pertemuan_ke')
                            ->label('Pertemuan Ke')
                            ->numeric()
                            ->required()
                            ->disabled($this->is_non_school_day)
                            ->helperText($this->is_non_school_day ? 'Tidak ada pertemuan pada hari libur' : 'Otomatis bertambah sesuai hari sekolah'),
                    ]),
                Section::make('Informasi Kelas')
                    ->visible(fn() => $this->kelas_info !== null)
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('kelas_nama')
                                ->label('Nama Kelas')
                                ->default(fn() => $this->kelas_nama)
                                ->disabled(),
                            TextInput::make('jumlah_siswa')
                                ->label('Jumlah Siswa')
                                ->default(fn() => $this->jumlah_siswa)
                                ->disabled(),
                            TextInput::make('status_hari')
                                ->label('Status Hari')
                                ->default(fn() => $this->status_hari) // SEKARANG AKAN BEKERJA
                                ->disabled(),
                        ]),
                    ]),
            ]);
    }

    // PERUBAHAN UTAMA: Modifikasi method savePresensi
    public function savePresensi()
    {
        // Check if it's a non-school day
        if ($this->is_non_school_day) {
            $message = 'Tidak dapat melakukan presensi pada ';
            if ($this->is_weekend) {
                $message .= 'akhir pekan: ' . $this->weekend_info->day_name;
            } else {
                $message .= 'hari libur: ' . $this->holiday_info?->nama_hari_libur;
            }

            Notification::make()
                ->title($message)
                ->warning()
                ->send();
            return;
        }

        // Get wali kelas id
        $waliKelasId = auth()->user()->waliKelas->id;

        foreach ($this->siswa_list as $siswa) {
            // SKIP SISWA YANG TIDAK MEMILIKI STATUS (HARI LIBUR)
            if ($siswa['status'] === null) {
                continue;
            }

            // Check if record already exists
            $presensi = Presensi::where('siswa_id', $siswa['siswa_id'])
                ->where('tanggal_presensi', $this->tanggal)
                ->first();

            if ($presensi) {
                // Update existing record
                $presensi->update([
                    'status' => $siswa['status'],
                    'keterangan' => $siswa['keterangan'] ?? null,
                    'pertemuan_ke' => $this->pertemuan_ke,
                ]);
            } else {
                // Create new record
                Presensi::create([
                    'siswa_id' => $siswa['siswa_id'],
                    'kelas_id' => $this->kelas_id,
                    'wali_kelas_id' => $waliKelasId,
                    'tanggal_presensi' => $this->tanggal,
                    'status' => $siswa['status'],
                    'keterangan' => $siswa['keterangan'] ?? null,
                    'pertemuan_ke' => $this->pertemuan_ke,
                ]);
            }
        }

        Notification::make()
            ->title('Presensi berhasil disimpan!')
            ->success()
            ->send();

        $this->loadSiswaList(); // Reload to reflect changes
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Siswa::query()->where('kelas_id', $this->kelas_id)->where('is_active', true))
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable(),
                TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'Hadir' => 'success',
                        'Izin' => 'info',
                        'Sakit' => 'warning',
                        'Tanpa Keterangan' => 'danger',
                        null => 'gray', // TAMBAHKAN untuk status null
                        default => 'gray',
                    })
                    ->state(function (Siswa $record): ?string {
                        foreach ($this->siswa_list as $siswa) {
                            if ($siswa['siswa_id'] === $record->id) {
                                return $siswa['status'] ?? 'Libur'; // TAMPILKAN 'Libur' jika status null
                            }
                        }
                        return 'Tanpa Keterangan';
                    })
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->state(function (Siswa $record): string {
                        foreach ($this->siswa_list as $siswa) {
                            if ($siswa['siswa_id'] === $record->id) {
                                return $siswa['keterangan'] ?? '';
                            }
                        }
                        return '';
                    })
                    ->wrap(), // TAMBAHKAN wrap untuk keterangan panjang
                TextColumn::make('izin_status')
                    ->label('Status Izin')
                    ->state(function (Siswa $record): string {
                        foreach ($this->siswa_list as $siswa) {
                            if ($siswa['siswa_id'] === $record->id) {
                                if ($siswa['has_izin']) {
                                    $izin = $siswa['izin_info'];
                                    return "✓ Izin Disetujui ({$izin['tanggal_mulai']} - {$izin['tanggal_selesai']})";
                                }
                                return '-';
                            }
                        }
                        return '-';
                    })
                    ->color('success')
                    ->icon(function (Siswa $record): ?string {
                        foreach ($this->siswa_list as $siswa) {
                            if ($siswa['siswa_id'] === $record->id && $siswa['has_izin']) {
                                return 'heroicon-o-check-circle';
                            }
                        }
                        return null;
                    }),
            ])
            ->filters([
                Filter::make('status')
                    ->form([
                        Radio::make('status')
                            ->label('Status Kehadiran')
                            ->options([
                                'all' => 'Semua',
                                'hadir' => 'Hadir',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'tanpa_keterangan' => 'Tanpa Keterangan',
                                'libur' => 'Hari Libur', // TAMBAHKAN filter untuk hari libur
                                'has_approved_izin' => 'Memiliki Izin Disetujui',
                            ])
                            ->default('all'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['status'] === 'all' || empty($data['status'])) {
                            return $query;
                        }

                        if ($data['status'] === 'has_approved_izin') {
                            $siswaIds = collect($this->siswa_list)
                                ->filter(fn($siswa) => $siswa['has_izin'])
                                ->pluck('siswa_id')
                                ->toArray();
                            return $query->whereIn('id', $siswaIds);
                        }

                        // TAMBAHKAN filter untuk hari libur
                        if ($data['status'] === 'libur') {
                            $siswaIds = collect($this->siswa_list)
                                ->filter(fn($siswa) => $siswa['status'] === null)
                                ->pluck('siswa_id')
                                ->toArray();
                            return $query->whereIn('id', $siswaIds);
                        }

                        // Filter siswa based on status
                        $siswaIds = collect($this->siswa_list)
                            ->filter(function ($siswa) use ($data) {
                                $status = strtolower($siswa['status'] ?? '');
                                $filterStatus = $data['status'];

                                if ($filterStatus === 'tanpa_keterangan') {
                                    return $status === 'tanpa keterangan';
                                }

                                return $status === $filterStatus;
                            })
                            ->pluck('siswa_id')
                            ->toArray();

                        return $query->whereIn('id', $siswaIds);
                    }),
            ])
            ->actions([
                Action::make('edit_presensi')
                    ->label('Edit Presensi')
                    ->icon('heroicon-o-pencil')
                    ->disabled(fn(Siswa $record) => $this->is_non_school_day || $this->getStatusForRecord($record) === null) // DISABLE untuk hari libur
                    ->form([
                        Radio::make('status')
                            ->label('Status Kehadiran')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Izin' => 'Izin',
                                'Sakit' => 'Sakit',
                                'Tanpa Keterangan' => 'Tanpa Keterangan',
                            ])
                            ->required(),
                        TextInput::make('keterangan')
                            ->label('Keterangan')
                            ->maxLength(255),
                    ])
                    ->fillForm(function (Siswa $record): array {
                        foreach ($this->siswa_list as $siswa) {
                            if ($siswa['siswa_id'] === $record->id) {
                                return [
                                    'status' => $siswa['status'] ?? 'Hadir',
                                    'keterangan' => $siswa['keterangan'],
                                ];
                            }
                        }
                        return ['status' => 'Hadir', 'keterangan' => ''];
                    })
                    ->action(function (Siswa $record, array $data): void {
                        // Update local siswa_list first
                        foreach ($this->siswa_list as $key => $siswa) {
                            if ($siswa['siswa_id'] === $record->id) {
                                $this->siswa_list[$key]['status'] = $data['status'];
                                $this->siswa_list[$key]['keterangan'] = $data['keterangan'] ?? '';
                                break;
                            }
                        }

                        Notification::make()
                            ->title('Status presensi diperbarui!')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('refresh_data')
                    ->label('Refresh Data')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        $this->loadSiswaList();
                        $this->checkNonSchoolDay();
                        $this->setPertemuanKe();

                        Notification::make()
                            ->title('Data berhasil diperbarui!')
                            ->success()
                            ->send();
                    }),
                Action::make('save_all')
                    ->label('Simpan Presensi')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(fn() => $this->savePresensi())
                    ->disabled(fn() => $this->is_non_school_day),
            ]);
    }

    // TAMBAHKAN HELPER METHOD
    private function getStatusForRecord(Siswa $record): ?string
    {
        foreach ($this->siswa_list as $siswa) {
            if ($siswa['siswa_id'] === $record->id) {
                return $siswa['status'];
            }
        }
        return null;
    }
}
