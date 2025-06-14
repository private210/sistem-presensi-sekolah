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

    // Properties untuk form
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

    // Properties untuk informasi kelas
    public $kelas_nama;
    public $jumlah_siswa;
    public $status_hari = 'Hari Sekolah';

    /**
     * Initialize component saat pertama kali dimuat
     */
    public function mount()
    {
        // Set locale untuk Carbon ke bahasa Indonesia
        Carbon::setLocale('id');

        // Cek authorization
        if (!auth()->user()->hasRole('Wali Kelas') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        // Set tanggal default ke hari ini
        $this->tanggal = Carbon::today()->format('Y-m-d');

        // Ambil data wali kelas yang login
        $waliKelas = auth()->user()->waliKelas;
        if ($waliKelas) {
            $this->kelas_id = $waliKelas->kelas_id;
            $this->kelas_info = Kelas::find($this->kelas_id);

            if ($this->kelas_info) {
                $this->kelas_nama = $this->kelas_info->nama_kelas;
                $this->loadSiswaList();
                $this->jumlah_siswa = count($this->siswa_list);
                $this->checkNonSchoolDay();
                $this->setPertemuanKe();
                $this->autoCreatePastAttendance();
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

    /**
     * Auto create attendance untuk tanggal yang sudah lewat
     */
    protected function autoCreatePastAttendance()
    {
        $today = Carbon::today();
        $selectedDate = Carbon::parse($this->tanggal);

        // Jika tanggal yang dipilih adalah hari ini atau masa depan, skip
        if ($selectedDate->gte($today)) {
            return;
        }

        // Jika hari libur, skip
        if ($this->is_non_school_day) {
            return;
        }

        $waliKelasId = auth()->user()->waliKelas->id;

        // Ambil semua siswa aktif di kelas
        $siswa = Siswa::where('kelas_id', $this->kelas_id)
            ->where('is_active', true)
            ->get();

        foreach ($siswa as $s) {
            // Cek apakah sudah ada presensi
            $existingPresensi = Presensi::where('siswa_id', $s->id)
                ->where('tanggal_presensi', $this->tanggal)
                ->first();

            if ($existingPresensi) {
                continue; // Skip jika sudah ada
            }

            // Cek apakah ada izin yang disetujui
            $izin = Izin::where('siswa_id', $s->id)
                ->where('status', 'Disetujui')
                ->where('tanggal_mulai', '<=', $this->tanggal)
                ->where('tanggal_selesai', '>=', $this->tanggal)
                ->first();

            $status = 'Hadir';
            $keterangan = 'Presensi Otomatis Hadir (Karena tidak melakukan presensi manual)';

            if ($izin) {
                $status = $izin->jenis_izin;
                $keterangan = $izin->keterangan;
            }

            // Create presensi otomatis
            Presensi::create([
                'siswa_id' => $s->id,
                'kelas_id' => $this->kelas_id,
                'wali_kelas_id' => $waliKelasId,
                'tanggal_presensi' => $this->tanggal,
                'status' => $status,
                'keterangan' => $keterangan,
                'pertemuan_ke' => $this->pertemuan_ke,
            ]);
        }
    }

    /**
     * Load data siswa beserta status presensi dan izin
     */
    protected function loadSiswaList()
    {
        $siswa = Siswa::where('kelas_id', $this->kelas_id)
            ->where('is_active', true)
            ->orderBy('nama_lengkap')
            ->get();

        $this->siswa_list = [];

        foreach ($siswa as $s) {
            // Cek presensi yang sudah ada
            $presensi = Presensi::where('siswa_id', $s->id)
                ->where('tanggal_presensi', $this->tanggal)
                ->first();

            // Cek izin yang disetujui
            $izin = Izin::where('siswa_id', $s->id)
                ->where('status', 'Disetujui')
                ->where('tanggal_mulai', '<=', $this->tanggal)
                ->where('tanggal_selesai', '>=', $this->tanggal)
                ->first();

            $status = null;
            $keterangan = '';
            $has_izin = false;
            $izin_id = null;

            // Set default status berdasarkan kondisi hari
            if ($this->is_non_school_day) {
                $status = null; // Null untuk hari libur
                if ($this->is_weekend) {
                    $keterangan = 'Hari libur akhir pekan - ' . $this->weekend_info->day_name;
                } elseif ($this->is_holiday) {
                    $keterangan = 'Hari libur resmi - ' . $this->holiday_info->nama_hari_libur;
                }
            } else {
                $status = 'Hadir'; // Default hadir untuk hari sekolah
            }

            // Override dengan izin jika ada
            if ($izin && !$this->is_non_school_day) {
                $status = $izin->jenis_izin;
                $keterangan = $izin->keterangan;
                $has_izin = true;
                $izin_id = $izin->id;
            }

            // Override dengan presensi yang sudah ada
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

        $this->jumlah_siswa = count($this->siswa_list);
    }

    /**
     * Cek apakah tanggal yang dipilih adalah hari libur
     */
    protected function checkNonSchoolDay()
    {
        $date = Carbon::parse($this->tanggal);

        // Reset status
        $this->is_holiday = false;
        $this->is_weekend = false;
        $this->is_non_school_day = false;
        $this->holiday_info = null;
        $this->weekend_info = null;

        // Cek weekend
        if ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY) {
            $this->is_weekend = true;
            $this->is_non_school_day = true;
            $this->weekend_info = (object)[
                'nama_hari_libur' => 'Hari Libur Akhir Pekan',
                'keterangan' => 'Hari ' . $date->translatedFormat('l') . ' - Tidak ada kegiatan pembelajaran',
                'day_name' => $date->translatedFormat('l')
            ];
        }

        // Cek hari libur resmi
        $holiday = HariLibur::where('tanggal', $this->tanggal)->first();
        if ($holiday) {
            $this->is_holiday = true;
            $this->is_non_school_day = true;
            $this->holiday_info = $holiday;
        }

        $this->updateStatusHari();
    }

    /**
     * Update status hari untuk ditampilkan di form
     */
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

    /**
     * Helper untuk cek apakah tanggal tertentu adalah hari libur
     */
    protected function isNonSchoolDay(Carbon $date): bool
    {
        // Cek weekend
        if ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY) {
            return true;
        }

        // Cek hari libur
        $holiday = HariLibur::where('tanggal', $date->format('Y-m-d'))->first();
        if ($holiday) {
            return true;
        }

        return false;
    }

    /**
     * Hitung pertemuan ke berapa berdasarkan hari sekolah
     */
    protected function setPertemuanKe()
    {
        $selectedDate = Carbon::parse($this->tanggal);
        $firstDayOfMonth = $selectedDate->copy()->firstOfMonth();

        // Cari hari sekolah pertama di bulan ini
        $currentDay = $firstDayOfMonth->copy();
        $firstSchoolDay = null;
        while ($currentDay->lte($selectedDate)) {
            if (!$this->isNonSchoolDay($currentDay)) {
                $firstSchoolDay = $currentDay->copy();
                break;
            }
            $currentDay->addDay();
        }

        // Jika tanggal yang dipilih adalah hari sekolah pertama
        if ($firstSchoolDay && $selectedDate->equalTo($firstSchoolDay)) {
            $this->pertemuan_ke = 1;
        } else {
            // Hitung jumlah hari sekolah dari hari sekolah pertama sampai tanggal yang dipilih
            if ($firstSchoolDay && $selectedDate->gt($firstSchoolDay) && !$this->isNonSchoolDay($selectedDate)) {
                $schoolDaysCount = 0;
                $checkDate = $firstSchoolDay->copy()->addDay();

                while ($checkDate->lte($selectedDate)) {
                    if (!$this->isNonSchoolDay($checkDate)) {
                        $schoolDaysCount++;
                    }
                    $checkDate->addDay();
                }
                $this->pertemuan_ke = $schoolDaysCount + 1;
            } else {
                $this->pertemuan_ke = $this->isNonSchoolDay($selectedDate) ? 0 : 1;
            }
        }

        // Untuk hari libur, ambil pertemuan_ke terakhir
        if ($this->isNonSchoolDay($selectedDate)) {
            $latestPresensi = Presensi::where('kelas_id', $this->kelas_id)
                ->where('tanggal_presensi', '<', $this->tanggal)
                ->orderBy('tanggal_presensi', 'desc')
                ->first();
            $this->pertemuan_ke = $latestPresensi ? $latestPresensi->pertemuan_ke : 0;
        }
    }

    /**
     * Definisi form untuk input tanggal dan informasi kelas
     */
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
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->loadSiswaList();
                                $this->checkNonSchoolDay();
                                $this->setPertemuanKe();
                                $this->autoCreatePastAttendance();
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
                                ->default(fn() => $this->status_hari)
                                ->disabled(),
                        ]),
                    ]),
            ]);
    }

    /**
     * Simpan semua data presensi
     */
    public function savePresensi()
    {
        // Validasi tidak bisa simpan di hari libur
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

        $waliKelasId = auth()->user()->waliKelas->id;

        foreach ($this->siswa_list as $siswa) {
            // Skip jika status null
            if ($siswa['status'] === null) {
                continue;
            }

            // Cek apakah sudah ada presensi
            $presensi = Presensi::where('siswa_id', $siswa['siswa_id'])
                ->where('tanggal_presensi', $this->tanggal)
                ->first();

            if ($presensi) {
                // Update presensi yang sudah ada
                $presensi->update([
                    'status' => $siswa['status'],
                    'keterangan' => $siswa['keterangan'] ?? null,
                    'pertemuan_ke' => $this->pertemuan_ke,
                ]);
            } else {
                // Create presensi baru
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

        // Reload data
        $this->loadSiswaList();
    }

    /**
     * Auto create presensi untuk siswa yang belum ada presensinya
     */
    public function autoCreateMissingAttendance()
    {
        if ($this->is_non_school_day) {
            Notification::make()
                ->title('Tidak dapat membuat presensi otomatis pada hari libur')
                ->warning()
                ->send();
            return;
        }

        $waliKelasId = auth()->user()->waliKelas->id;
        $createdCount = 0;

        foreach ($this->siswa_list as $siswa) {
            // Skip jika sudah ada presensi
            if ($siswa['presensi_id']) {
                continue;
            }

            // Skip jika status null
            if ($siswa['status'] === null) {
                continue;
            }

            Presensi::create([
                'siswa_id' => $siswa['siswa_id'],
                'kelas_id' => $this->kelas_id,
                'wali_kelas_id' => $waliKelasId,
                'tanggal_presensi' => $this->tanggal,
                'status' => $siswa['status'],
                'keterangan' => $siswa['keterangan'] ?? 'Otomatis dibuat sistem',
                'pertemuan_ke' => $this->pertemuan_ke,
            ]);

            $createdCount++;
        }

        if ($createdCount > 0) {
            Notification::make()
                ->title("Berhasil membuat {$createdCount} record presensi otomatis")
                ->success()
                ->send();

            $this->loadSiswaList();
        } else {
            Notification::make()
                ->title('Tidak ada presensi yang perlu dibuat')
                ->info()
                ->send();
        }
    }

    /**
     * Definisi tabel untuk menampilkan data siswa
     */
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
                        'Libur' => 'gray',
                        null => 'gray',
                        default => 'gray',
                    })
                    ->state(function (Siswa $record): ?string {
                        foreach ($this->siswa_list as $siswa) {
                            if ($siswa['siswa_id'] === $record->id) {
                                return $siswa['status'] ?? 'Libur';
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
                    ->wrap(),
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
                                'libur' => 'Hari Libur',
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

                        if ($data['status'] === 'libur') {
                            $siswaIds = collect($this->siswa_list)
                                ->filter(fn($siswa) => $siswa['status'] === null)
                                ->pluck('siswa_id')
                                ->toArray();
                            return $query->whereIn('id', $siswaIds);
                        }

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
                    ->disabled(fn(Siswa $record) => $this->is_non_school_day || $this->getStatusForRecord($record) === null)
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
                        // Update status di array siswa_list
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
                Action::make('auto_create_attendance')
                    ->label('Buat Presensi Otomatis')
                    ->color('warning')
                    ->icon('heroicon-o-user-plus')
                    ->action(fn() => $this->autoCreateMissingAttendance())
                    ->disabled(fn() => $this->is_non_school_day)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Buat Presensi Otomatis')
                    ->modalDescription('Sistem akan membuat record presensi "Hadir" untuk siswa yang belum memiliki presensi pada tanggal ini. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Buat Otomatis'),
                Action::make('save_all')
                    ->label('Simpan Presensi')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(fn() => $this->savePresensi())
                    ->disabled(fn() => $this->is_non_school_day),
            ]);
    }

    /**
     * Helper untuk mendapatkan status dari record siswa
     */
    private function getStatusForRecord(Siswa $record): ?string
    {
        foreach ($this->siswa_list as $siswa) {
            if ($siswa['siswa_id'] === $record->id) {
                return $siswa['status'];
            }
        }
        return null;
    }

    /**
     * Static method untuk auto generate presensi harian (bisa dipanggil dari scheduler)
     */
    public static function autoGenerateDailyAttendance($date = null)
    {
        $date = $date ?: Carbon::today()->format('Y-m-d');
        $carbonDate = Carbon::parse($date);

        // Skip jika weekend
        if ($carbonDate->dayOfWeek === Carbon::SATURDAY || $carbonDate->dayOfWeek === Carbon::SUNDAY) {
            return false;
        }

        // Skip jika hari libur
        $holiday = HariLibur::where('tanggal', $date)->first();
        if ($holiday) {
            return false;
        }

        // Process untuk semua wali kelas
        $waliKelasList = WaliKelas::with('kelas')->get();

        foreach ($waliKelasList as $waliKelas) {
            if (!$waliKelas->kelas) continue;

            // Ambil semua siswa aktif di kelas
            $siswaList = Siswa::where('kelas_id', $waliKelas->kelas_id)
                ->where('is_active', true)
                ->get();

            // Cari pertemuan_ke terakhir
            $latestPresensi = Presensi::where('kelas_id', $waliKelas->kelas_id)
                ->where('tanggal_presensi', '<', $date)
                ->orderBy('tanggal_presensi', 'desc')
                ->orderBy('pertemuan_ke', 'desc')
                ->first();

            $pertemuan_ke = $latestPresensi ? $latestPresensi->pertemuan_ke + 1 : 1;

            foreach ($siswaList as $siswa) {
                // Cek apakah sudah ada presensi
                $existingPresensi = Presensi::where('siswa_id', $siswa->id)
                    ->where('tanggal_presensi', $date)
                    ->first();

                if ($existingPresensi) {
                    continue;
                }

                // Cek izin
                $izin = Izin::where('siswa_id', $siswa->id)
                    ->where('status', 'Disetujui')
                    ->where('tanggal_mulai', '<=', $date)
                    ->where('tanggal_selesai', '>=', $date)
                    ->first();

                $status = 'Hadir';
                $keterangan = 'Otomatis hadir (sistem)';

                if ($izin) {
                    $status = $izin->jenis_izin;
                    $keterangan = $izin->keterangan;
                }

                // Create presensi
                Presensi::create([
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $waliKelas->kelas_id,
                    'wali_kelas_id' => $waliKelas->id,
                    'tanggal_presensi' => $date,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'pertemuan_ke' => $pertemuan_ke,
                ]);
            }
        }

        return true;
    }
}
