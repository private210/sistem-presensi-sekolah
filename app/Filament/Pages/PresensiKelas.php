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
    protected static ?string $navigationGroup = 'Manajemen Presensi';
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

    // Properties untuk tracking status penyimpanan - PERBAIKI BAGIAN INI
    public $is_saved = false;
    public $has_complete_attendance = false; // Tambah property baru
    public $saved_dates = [];

    /**
     * Initialize component saat pertama kali dimuat
     */
    public function mount()
    {
        Carbon::setLocale('id');

        if (!auth()->user()->hasRole('Wali Kelas') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini');
        }

        $this->tanggal = Carbon::today()->format('Y-m-d');
        $this->saved_dates = session()->get('presensi_saved_dates', []);

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
                $this->checkIfAlreadySaved();
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
     * PERBAIKI METHOD INI - Check apakah presensi untuk tanggal ini sudah lengkap
     */
    protected function checkIfAlreadySaved()
    {
        if ($this->is_non_school_day) {
            $this->is_saved = true;
            $this->has_complete_attendance = true;
            return;
        }

        // Hitung total siswa aktif
        $totalSiswa = Siswa::where('kelas_id', $this->kelas_id)
            ->where('is_active', true)
            ->count();

        // Hitung total presensi yang sudah ada untuk tanggal ini
        $totalPresensi = Presensi::where('kelas_id', $this->kelas_id)
            ->where('tanggal_presensi', $this->tanggal)
            ->count();

        // Set status berdasarkan kelengkapan data
        $this->has_complete_attendance = ($totalSiswa > 0 && $totalSiswa === $totalPresensi);
        $this->is_saved = $this->has_complete_attendance;

        // Tambahan: cek juga dari session untuk memastikan
        if (!$this->is_saved && in_array($this->tanggal, $this->saved_dates)) {
            // Double check dari database
            $totalPresensiCheck = Presensi::where('kelas_id', $this->kelas_id)
                ->where('tanggal_presensi', $this->tanggal)
                ->count();

            if ($totalPresensiCheck >= $totalSiswa) {
                $this->is_saved = true;
                $this->has_complete_attendance = true;
            }
        }
    }

    /**
     * PERBAIKI METHOD INI - Auto create attendance untuk tanggal yang sudah lewat
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

        // Cek apakah sudah ada presensi lengkap untuk tanggal ini
        $totalSiswa = Siswa::where('kelas_id', $this->kelas_id)
            ->where('is_active', true)
            ->count();

        $totalPresensi = Presensi::where('kelas_id', $this->kelas_id)
            ->where('tanggal_presensi', $this->tanggal)
            ->count();

        // Jika sudah lengkap, skip
        if ($totalPresensi >= $totalSiswa) {
            return;
        }

        $waliKelasId = auth()->user()->waliKelas->id;
        $siswa = Siswa::where('kelas_id', $this->kelas_id)
            ->where('is_active', true)
            ->get();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($siswa as $s) {
            // Cek apakah sudah ada presensi
            $existingPresensi = Presensi::where('siswa_id', $s->id)
                ->where('tanggal_presensi', $this->tanggal)
                ->first();

            // Skip jika sudah ada presensi
            if ($existingPresensi) {
                continue;
            }

            // Cek apakah ada izin yang disetujui
            $izin = Izin::where('siswa_id', $s->id)
                ->where('status', 'Disetujui')
                ->where('tanggal_mulai', '<=', $this->tanggal)
                ->where('tanggal_selesai', '>=', $this->tanggal)
                ->first();

            $status = 'Hadir';
            $keterangan = 'Presensi otomatis (tanggal terlewat)';

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
            $createdCount++;
        }

        // Notifikasi jika ada yang dibuat
        if ($createdCount > 0) {
            $message = "Auto presensi untuk tanggal " . Carbon::parse($this->tanggal)->format('d/m/Y') . ": {$createdCount} siswa dibuat otomatis";

            Notification::make()
                ->title('Presensi Otomatis')
                ->body($message)
                ->info()
                ->send();
        }
    }

    /**
     * HAPUS METHOD INI atau buat optional - Batch auto create untuk multiple dates
     */
    public function batchAutoCreatePastAttendance()
    {
        // Bisa dihapus atau dibuat optional/admin only
        if (!auth()->user()->hasRole('super_admin')) {
            Notification::make()
                ->title('Akses ditolak')
                ->body('Fitur ini hanya untuk admin')
                ->warning()
                ->send();
            return;
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::today()->startOfMonth();

        $currentDate = $startOfMonth->copy();
        $processedDates = [];
        $totalCreated = 0;

        while ($currentDate->lt($today)) {
            if (!$this->isNonSchoolDay($currentDate)) {
                $originalDate = $this->tanggal;
                $this->tanggal = $currentDate->format('Y-m-d');

                $this->checkNonSchoolDay();
                $this->setPertemuanKe();

                $beforeCount = Presensi::where('kelas_id', $this->kelas_id)
                    ->where('tanggal_presensi', $this->tanggal)
                    ->count();

                $this->autoCreatePastAttendance();

                $afterCount = Presensi::where('kelas_id', $this->kelas_id)
                    ->where('tanggal_presensi', $this->tanggal)
                    ->count();

                if ($afterCount > $beforeCount) {
                    $processedDates[] = $currentDate->format('d/m/Y');
                    $totalCreated += ($afterCount - $beforeCount);
                }

                $this->tanggal = $originalDate;
            }

            $currentDate->addDay();
        }

        if (count($processedDates) > 0) {
            Notification::make()
                ->title('Batch Auto Presensi Selesai')
                ->body("Diproses: {$totalCreated} presensi untuk " . count($processedDates) . " hari sekolah")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Tidak ada presensi yang perlu dibuat')
                ->info()
                ->send();
        }

        $this->checkNonSchoolDay();
        $this->setPertemuanKe();
        $this->loadSiswaList();
        $this->checkIfAlreadySaved();
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
            $presensi = Presensi::where('siswa_id', $s->id)
                ->where('tanggal_presensi', $this->tanggal)
                ->first();

            $izin = Izin::where('siswa_id', $s->id)
                ->where('status', 'Disetujui')
                ->where('tanggal_mulai', '<=', $this->tanggal)
                ->where('tanggal_selesai', '>=', $this->tanggal)
                ->first();

            $status = null;
            $keterangan = '';
            $has_izin = false;
            $izin_id = null;

            if ($this->is_non_school_day) {
                $status = null;
                if ($this->is_weekend) {
                    $keterangan = 'Hari libur akhir pekan - ' . $this->weekend_info->day_name;
                } elseif ($this->is_holiday) {
                    $keterangan = 'Hari libur resmi - ' . $this->holiday_info->nama_hari_libur;

                    if (
                        $this->holiday_info->tanggal_selesai &&
                        !$this->holiday_info->tanggal_mulai->equalTo($this->holiday_info->tanggal_selesai)
                    ) {
                        $keterangan .= ' (' . $this->holiday_info->tanggal_mulai->format('d M') .
                            ' - ' . $this->holiday_info->tanggal_selesai->format('d M Y') . ')';
                    }
                }
            } else {
                $status = 'Hadir';
            }

            if ($izin && !$this->is_non_school_day) {
                $status = $izin->jenis_izin;
                $keterangan = $izin->keterangan;
                $has_izin = true;
                $izin_id = $izin->id;
            }

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

    protected function checkNonSchoolDay()
    {
        $date = Carbon::parse($this->tanggal);

        $this->is_holiday = false;
        $this->is_weekend = false;
        $this->is_non_school_day = false;
        $this->holiday_info = null;
        $this->weekend_info = null;

        if ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY) {
            $this->is_weekend = true;
            $this->is_non_school_day = true;
            $this->weekend_info = (object)[
                'nama_hari_libur' => 'Hari Libur Akhir Pekan',
                'keterangan' => 'Hari ' . $date->translatedFormat('l') . ' - Tidak ada kegiatan pembelajaran',
                'day_name' => $date->translatedFormat('l')
            ];
        }

        $holiday = HariLibur::where('tanggal_mulai', '<=', $this->tanggal)
            ->where(function ($query) {
                $query->whereNull('tanggal_selesai')
                    ->where('tanggal_mulai', '=', $this->tanggal)
                    ->orWhere('tanggal_selesai', '>=', $this->tanggal);
            })
            ->first();

        if ($holiday) {
            $this->is_holiday = true;
            $this->is_non_school_day = true;
            $this->holiday_info = $holiday;
        }

        $this->updateStatusHari();
    }

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

    protected function isNonSchoolDay(Carbon $date): bool
    {
        if ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY) {
            return true;
        }

        $holiday = HariLibur::where('tanggal_mulai', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('tanggal_selesai')
                    ->where('tanggal_mulai', '=', $date->format('Y-m-d'))
                    ->orWhere('tanggal_selesai', '>=', $date->format('Y-m-d'));
            })
            ->exists();

        return $holiday;
    }

    protected function setPertemuanKe()
    {
        $selectedDate = Carbon::parse($this->tanggal);
        $firstDayOfMonth = $selectedDate->copy()->firstOfMonth();

        $currentDay = $firstDayOfMonth->copy();
        $firstSchoolDay = null;
        while ($currentDay->lte($selectedDate)) {
            if (!$this->isNonSchoolDay($currentDay)) {
                $firstSchoolDay = $currentDay->copy();
                break;
            }
            $currentDay->addDay();
        }

        if ($firstSchoolDay && $selectedDate->equalTo($firstSchoolDay)) {
            $this->pertemuan_ke = 1;
        } else {
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

        if ($this->isNonSchoolDay($selectedDate)) {
            $latestPresensi = Presensi::where('kelas_id', $this->kelas_id)
                ->where('tanggal_presensi', '<', $this->tanggal)
                ->orderBy('tanggal_presensi', 'desc')
                ->first();
            $this->pertemuan_ke = $latestPresensi ? $latestPresensi->pertemuan_ke : 0;
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
                            ->timezone('Asia/Jakarta')
                            ->format('d/m/Y')
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(function () {
                                $this->loadSiswaList();
                                $this->checkNonSchoolDay();
                                $this->setPertemuanKe();
                                $this->autoCreatePastAttendance();
                                $this->checkIfAlreadySaved();
                            }),
                        TextInput::make('pertemuan_ke')
                            ->label('Hari Ke')
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
     * PERBAIKI METHOD INI - Simpan semua data presensi
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

        // Validasi jika sudah tersimpan lengkap
        if ($this->has_complete_attendance) {
            Notification::make()
                ->title('Presensi sudah lengkap!')
                ->body('Semua siswa sudah memiliki data presensi untuk tanggal ini.')
                ->info()
                ->send();
            return;
        }

        $waliKelasId = auth()->user()->waliKelas->id;
        $savedCount = 0;
        $updatedCount = 0;

        foreach ($this->siswa_list as $siswa) {
            // Skip jika status null
            if ($siswa['status'] === null) {
                continue;
            }

            $presensi = Presensi::where('siswa_id', $siswa['siswa_id'])
                ->where('tanggal_presensi', $this->tanggal)
                ->first();

            if ($presensi) {
                $presensi->update([
                    'status' => $siswa['status'],
                    'keterangan' => $siswa['keterangan'] ?? null,
                    'pertemuan_ke' => $this->pertemuan_ke,
                ]);
                $updatedCount++;
            } else {
                Presensi::create([
                    'siswa_id' => $siswa['siswa_id'],
                    'kelas_id' => $this->kelas_id,
                    'wali_kelas_id' => $waliKelasId,
                    'tanggal_presensi' => $this->tanggal,
                    'status' => $siswa['status'],
                    'keterangan' => $siswa['keterangan'] ?? null,
                    'pertemuan_ke' => $this->pertemuan_ke,
                ]);
                $savedCount++;
            }
        }

        // PENTING: Set status setelah berhasil simpan
        $this->is_saved = true;
        $this->has_complete_attendance = true;

        // Simpan ke session
        if (!in_array($this->tanggal, $this->saved_dates)) {
            $this->saved_dates[] = $this->tanggal;
            session()->put('presensi_saved_dates', $this->saved_dates);
        }

        $message = 'Presensi berhasil disimpan! ';
        if ($savedCount > 0) {
            $message .= "{$savedCount} siswa ditambahkan";
        }
        if ($updatedCount > 0) {
            $message .= ($savedCount > 0 ? ", " : "") . "{$updatedCount} siswa diperbarui";
        }

        Notification::make()
            ->title($message)
            ->success()
            ->send();

        // Reload data
        $this->loadSiswaList();
        $this->checkIfAlreadySaved(); // Re-check status
    }

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
            if ($siswa['presensi_id']) {
                continue;
            }

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
            $this->checkIfAlreadySaved();
        } else {
            Notification::make()
                ->title('Tidak ada presensi yang perlu dibuat')
                ->info()
                ->send();
        }
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
                        'Alpa' => 'danger',
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
                                'alpa' => 'Alpa (Tanpa Keterangan)',
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

                                if ($filterStatus === 'alpa') {
                                    return $status === 'alpa';
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
                                'Alpa' => 'Alpa',
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
                        foreach ($this->siswa_list as $key => $siswa) {
                            if ($siswa['siswa_id'] === $record->id) {
                                $this->siswa_list[$key]['status'] = $data['status'];
                                $this->siswa_list[$key]['keterangan'] = $data['keterangan'] ?? '';
                                break;
                            }
                        }

                        // Set ulang status jika ada perubahan
                        $this->checkIfAlreadySaved();

                        Notification::make()
                            ->title('Status presensi diperbarui!')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                // UBAH/HAPUS ACTION INI - Batch auto create (buat optional/admin only)
                Action::make('batch_auto_create')
                    ->label('Auto Presensi Bulan Ini')
                    ->color('warning')
                    ->icon('heroicon-o-calendar-days')
                    ->action(fn() => $this->batchAutoCreatePastAttendance())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Auto Presensi')
                    ->modalDescription('Sistem akan membuat presensi otomatis untuk semua tanggal yang terlewat di bulan ini. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya, Proses Otomatis')
                    ->visible(fn() => auth()->user()->hasRole('super_admin')), // HANYA ADMIN

                Action::make('refresh_data')
                    ->label('Refresh Data')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function () {
                        $this->loadSiswaList();
                        $this->checkNonSchoolDay();
                        $this->setPertemuanKe();
                        $this->checkIfAlreadySaved();

                        Notification::make()
                            ->title('Data berhasil diperbarui!')
                            ->success()
                            ->send();
                    }),

                // PERBAIKI ACTION INI - Tombol simpan dengan kondisi yang tepat
                Action::make('save_all')
                    ->label(function () {
                        if ($this->has_complete_attendance) {
                            return 'Presensi Sudah Lengkap';
                        } elseif ($this->is_saved) {
                            return 'Presensi Sudah Disimpan';
                        } else {
                            return 'Simpan Presensi';
                        }
                    })
                    ->color(function () {
                        if ($this->has_complete_attendance || $this->is_saved) {
                            return 'success'; // Hijau untuk yang sudah selesai
                        } else {
                            return 'primary'; // Biru untuk yang belum
                        }
                    })
                    ->icon(function () {
                        if ($this->has_complete_attendance || $this->is_saved) {
                            return 'heroicon-o-check-circle';
                        } else {
                            return 'heroicon-o-check';
                        }
                    })
                    ->action(fn() => $this->savePresensi())
                    ->disabled(function () {
                        return $this->is_non_school_day || $this->has_complete_attendance;
                    })
                    ->tooltip(function () {
                        if ($this->is_non_school_day) {
                            return 'Tidak dapat melakukan presensi pada hari libur';
                        } elseif ($this->has_complete_attendance) {
                            return 'Presensi untuk tanggal ini sudah lengkap';
                        } else {
                            return null;
                        }
                    }),
            ]);
    }

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
     * PERBAIKI METHOD INI - Static method untuk auto generate presensi harian
     */
    public static function autoGenerateDailyAttendance($date = null)
    {
        $date = $date ?: Carbon::yesterday()->format('Y-m-d'); // DEFAULT KE KEMARIN
        $carbonDate = Carbon::parse($date);

        // Skip jika weekend
        if ($carbonDate->dayOfWeek === Carbon::SATURDAY || $carbonDate->dayOfWeek === Carbon::SUNDAY) {
            return false;
        }

        // Skip jika hari libur
        $holiday = HariLibur::where('tanggal_mulai', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('tanggal_selesai')
                    ->where('tanggal_mulai', '=', $date)
                    ->orWhere('tanggal_selesai', '>=', $date);
            })
            ->exists();

        if ($holiday) {
            return false;
        }

        $waliKelasList = WaliKelas::with('kelas')->get();

        foreach ($waliKelasList as $waliKelas) {
            if (!$waliKelas->kelas) continue;

            // CEK APAKAH SUDAH ADA PRESENSI LENGKAP
            $totalSiswa = Siswa::where('kelas_id', $waliKelas->kelas_id)
                ->where('is_active', true)
                ->count();

            $existingPresensi = Presensi::where('kelas_id', $waliKelas->kelas_id)
                ->where('tanggal_presensi', $date)
                ->count();

            // SKIP jika sudah lengkap
            if ($existingPresensi >= $totalSiswa) {
                continue;
            }

            $siswaList = Siswa::where('kelas_id', $waliKelas->kelas_id)
                ->where('is_active', true)
                ->get();

            $pertemuan_ke = self::calculatePertemuanKe($waliKelas->kelas_id, $date);

            foreach ($siswaList as $siswa) {
                // Cek apakah sudah ada presensi
                $existingPresensi = Presensi::where('siswa_id', $siswa->id)
                    ->where('tanggal_presensi', $date)
                    ->first();

                if ($existingPresensi) {
                    continue; // SKIP jika sudah ada
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

    protected static function calculatePertemuanKe($kelasId, $date): int
    {
        $selectedDate = Carbon::parse($date);
        $firstDayOfMonth = $selectedDate->copy()->firstOfMonth();

        $schoolDaysCount = 0;
        $currentDay = $firstDayOfMonth->copy();

        while ($currentDay->lte($selectedDate)) {
            if ($currentDay->dayOfWeek === Carbon::SATURDAY || $currentDay->dayOfWeek === Carbon::SUNDAY) {
                $currentDay->addDay();
                continue;
            }

            $isHoliday = HariLibur::where('tanggal_mulai', '<=', $currentDay->format('Y-m-d'))
                ->where(function ($query) use ($currentDay) {
                    $query->whereNull('tanggal_selesai')
                        ->where('tanggal_mulai', '=', $currentDay->format('Y-m-d'))
                        ->orWhere('tanggal_selesai', '>=', $currentDay->format('Y-m-d'));
                })
                ->exists();

            if (!$isHoliday) {
                $schoolDaysCount++;
            }

            $currentDay->addDay();
        }

        return $schoolDaysCount;
    }
}

