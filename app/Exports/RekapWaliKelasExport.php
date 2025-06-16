<?php

namespace App\Exports;

use App\Models\Kelas;
use App\Models\WaliKelas;
use App\Models\KepalaSekolah;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class RekapWaliKelasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $data;
    protected $groupedData;
    protected $tanggal_mulai;
    protected $tanggal_selesai;
    protected $kelas;
    protected $wali_kelas;
    protected $all_wali_kelas;
    protected $kepala_sekolah;
    protected $row_count = 0;
    protected $periode_type;
    protected $total_school_days;
    protected $is_wali_murid = false; // Add flag untuk wali murid

    public function __construct($data, $tanggal_mulai, $tanggal_selesai, $kelas = null, $wali_kelas = null, $kepala_sekolah = null, $periode_type = 'bulan', $is_wali_murid = false)
    {
        $this->data = $data;
        $this->tanggal_mulai = $tanggal_mulai;
        $this->tanggal_selesai = $tanggal_selesai;
        $this->kelas = $kelas;
        $this->periode_type = $periode_type;
        $this->is_wali_murid = $is_wali_murid; // Set flag

        // Calculate total school days untuk periode
        $this->total_school_days = $this->calculateSchoolDays($tanggal_mulai, $tanggal_selesai);

        // Process data untuk grouping per siswa
        $this->groupedData = $this->data->groupBy('siswa_id')->map(function ($studentData) {
            $presensi = $studentData->first();
            $jumlah_hari_hadir = $studentData->count(); // Jumlah hari siswa ada data presensi
            $jumlah_hadir = $studentData->where('status', 'Hadir')->count();
            $jumlah_sakit = $studentData->where('status', 'Sakit')->count();
            $jumlah_izin = $studentData->where('status', 'Izin')->count();
            $jumlah_alpha = $studentData->whereIn('status', ['Alpa', 'Tanpa Keterangan'])->count();

            // Gunakan total school days untuk periode sebagai pembanding
            $percentage = $this->total_school_days > 0 ? ($jumlah_hadir / $this->total_school_days) * 100 : 0;
            $keterangan = 'Sangat Kurang';
            if ($percentage >= 90) $keterangan = 'Baik';
            elseif ($percentage >= 80) $keterangan = 'Cukup';
            elseif ($percentage >= 70) $keterangan = 'Kurang';

            return [
                'presensi' => $presensi,
                'siswa' => $presensi->siswa,
                'kelas' => $presensi->kelas,
                'jumlah_hari' => $this->total_school_days, // Total hari sekolah dalam periode
                'jumlah_hadir' => $jumlah_hadir,
                'jumlah_sakit' => $jumlah_sakit,
                'jumlah_izin' => $jumlah_izin,
                'jumlah_alpha' => $jumlah_alpha,
                'jumlah_total' => $jumlah_sakit + $jumlah_izin + $jumlah_alpha,
                'keterangan' => $keterangan
            ];
        })->values();

        $this->row_count = $this->groupedData->count();

        // Handle wali kelas
        if ($wali_kelas) {
            $this->wali_kelas = $wali_kelas;
        } elseif ($kelas) {
            $this->wali_kelas = WaliKelas::with('user')
                ->where('kelas_id', $kelas->id)
                ->where('is_active', true)
                ->first();
        } else {
            $this->wali_kelas = null;
        }

        // Handle multiple wali kelas untuk export semua kelas
        if (!$kelas && $this->data->isNotEmpty()) {
            $kelas_ids = $this->data->pluck('kelas_id')->unique();
            $this->all_wali_kelas = WaliKelas::with(['user', 'kelas'])
                ->whereIn('kelas_id', $kelas_ids)
                ->where('is_active', true)
                ->get();

            if ($kelas_ids->count() == 1 && !$this->wali_kelas) {
                $this->wali_kelas = $this->all_wali_kelas->first();
                $this->kelas = Kelas::find($kelas_ids->first());
            }
        } else {
            $this->all_wali_kelas = collect();
        }

        // Handle kepala sekolah
        if ($kepala_sekolah) {
            $this->kepala_sekolah = $kepala_sekolah;
        } else {
            $this->kepala_sekolah = KepalaSekolah::with('user')
                ->where('is_active', true)
                ->first();
        }
    }

    /**
     * Calculate total school days (weekdays minus holidays)
     */
    protected function calculateSchoolDays($start, $end)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        $schoolDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Skip weekends
            if ($current->isWeekend()) {
                $current->addDay();
                continue;
            }

            // Check if it's a holiday using HariLibur model
            $isHoliday = \App\Models\HariLibur::where('tanggal_mulai', '<=', $current->format('Y-m-d'))
                ->where(function ($query) use ($current) {
                    $query->whereNull('tanggal_selesai')
                        ->where('tanggal_mulai', '=', $current->format('Y-m-d'))
                        ->orWhere('tanggal_selesai', '>=', $current->format('Y-m-d'));
                })
                ->exists();

            // Only count if it's not a holiday
            if (!$isHoliday) {
                $schoolDays++;
            }

            $current->addDay();
        }

        return $schoolDays;
    }

    /**
     * Get period label
     */
    protected function getPeriodLabel()
    {
        if ($this->periode_type === 'semester') {
            $startMonth = Carbon::parse($this->tanggal_mulai)->month;
            if ($startMonth >= 7) {
                return 'Semester Ganjil';
            } else {
                return 'Semester Genap';
            }
        }
        return 'Bulanan';
    }

    public function collection()
    {
        return $this->groupedData;
    }

    public function headings(): array
    {
        return [];
    }

    public function map($item): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function title(): string
    {
        return 'Rekap Presensi';
    }

    // Helper methods remain the same
    private function getWaliKelasName()
    {
        if ($this->wali_kelas) {
            if ($this->wali_kelas->user && $this->wali_kelas->user->name) {
                return $this->wali_kelas->user->name;
            }
            if (isset($this->wali_kelas->nama_lengkap)) {
                return $this->wali_kelas->nama_lengkap;
            }
            if (isset($this->wali_kelas->name)) {
                return $this->wali_kelas->name;
            }
        }
        return 'NAMA WALI KELAS';
    }

    private function getWaliKelasNip()
    {
        if ($this->wali_kelas) {
            if ($this->wali_kelas->user && isset($this->wali_kelas->user->nip)) {
                return $this->wali_kelas->user->nip ?? 'N/A';
            }
            if (isset($this->wali_kelas->nip)) {
                return $this->wali_kelas->nip ?? 'N/A';
            }
        }
        return 'NIP WALI KELAS';
    }

    private function getKepalaSekolahName()
    {
        if ($this->kepala_sekolah) {
            if ($this->kepala_sekolah->user && $this->kepala_sekolah->user->name) {
                return $this->kepala_sekolah->user->name;
            }
            if (isset($this->kepala_sekolah->nama_lengkap)) {
                return $this->kepala_sekolah->nama_lengkap;
            }
            if (isset($this->kepala_sekolah->name)) {
                return $this->kepala_sekolah->name;
            }
        }
        return config('app.school_principal_name', 'NAMA KEPALA SEKOLAH');
    }

    private function getKepalaSekolahNip()
    {
        if ($this->kepala_sekolah) {
            if ($this->kepala_sekolah->user && isset($this->kepala_sekolah->user->nip)) {
                return $this->kepala_sekolah->user->nip ?? 'N/A';
            }
            if (isset($this->kepala_sekolah->nip)) {
                return $this->kepala_sekolah->nip ?? 'N/A';
            }
        }
        return config('app.school_principal_nip', 'NIP KEPALA SEKOLAH');
    }
    private function getKepalaSekolahPangkat()
    {
        if ($this->kepala_sekolah) {
            if ($this->kepala_sekolah->user && isset($this->kepala_sekolah->user->pangkat)) {
                return $this->kepala_sekolah->user->pangkat ?? 'N/A';
            }
            if (isset($this->kepala_sekolah->nip)) {
                return $this->kepala_sekolah->pangkat ?? 'N/A';
            }
        }
        return config('app.school_principal_pangkat', 'PANGKAT KEPALA SEKOLAH');
    }
    private function getKepalaSekolahGolongan()
    {
        if ($this->kepala_sekolah) {
            if ($this->kepala_sekolah->user && isset($this->kepala_sekolah->user->golongan)) {
                return $this->kepala_sekolah->user->golongan ?? 'N/A';
            }
            if (isset($this->kepala_sekolah->golongan)) {
                return $this->kepala_sekolah->golongan ?? 'N/A';
            }
        }
        return config('app.school_principal_golongan', 'GOLONGAN KEPALA SEKOLAH');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Clear any existing data
                $sheet->removeRow(1, $sheet->getHighestRow());

                // Set default font
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(10);

                // 1. HEADER
                $sheet->setCellValue('A1', 'REKAP PRESENSI SISWA');
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E40AF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $sheet->setCellValue('A2', config('app.name', 'Sistem Presensi Sekolah'));
                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 12, 'color' => ['rgb' => '64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // Blue line separator
                $sheet->mergeCells('A3:K3');
                $sheet->getRowDimension(3)->setRowHeight(2);
                $sheet->getStyle('A3:K3')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2563EB']
                    ]
                ]);

                // 2. INFORMASI HEADER dengan periode type
                $sheet->setCellValue('A5', 'Periode ' . $this->getPeriodLabel());
                $sheet->setCellValue('C5', ':');
                $sheet->setCellValue('D5', Carbon::parse($this->tanggal_mulai)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($this->tanggal_selesai)->translatedFormat('d F Y'));
                $sheet->mergeCells('D5:F5');

                $sheet->setCellValue('G5', 'Dicetak pada');
                $sheet->setCellValue('I5', ':');
                $sheet->setCellValue('J5', Carbon::now()->translatedFormat('d F Y') . ' Pukul ' . Carbon::now()->format('H:i'));
                $sheet->mergeCells('J5:K5');

                $sheet->setCellValue('A6', 'Kelas');
                $sheet->setCellValue('C6', ':');
                $sheet->setCellValue('D6', $this->kelas ? $this->kelas->nama_kelas : 'Semua Kelas');
                $sheet->mergeCells('D6:F6');

                $sheet->setCellValue('G6', 'Total Siswa');
                $sheet->setCellValue('I6', ':');
                $sheet->setCellValue('J6', $this->groupedData->count() . ' siswa');
                $sheet->mergeCells('J6:K6');

                $sheet->setCellValue('A7', 'Total Hari Sekolah');
                $sheet->setCellValue('C7', ':');
                $sheet->setCellValue('D7', $this->total_school_days . ' hari');
                $sheet->mergeCells('D7:F7');

                $sheet->setCellValue('G7', 'Dicetak oleh');
                $sheet->setCellValue('I7', ':');
                $sheet->setCellValue('J7', auth()->user()->name ?? 'Admin');
                $sheet->mergeCells('J7:K7');

                // Style for info section
                $sheet->getStyle('A5:K7')->applyFromArray([
                    'font' => ['size' => 10]
                ]);

                // 3. RINGKASAN STATISTIK
                $sheet->setCellValue('A9', '📊 Ringkasan Statistik');
                $sheet->mergeCells('A9:K9');
                $sheet->getStyle('A9')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E40AF']]
                ]);

                // Calculate statistics
                $total_hadir = $this->data->where('status', 'Hadir')->count();
                $total_izin = $this->data->where('status', 'Izin')->count();
                $total_sakit = $this->data->where('status', 'Sakit')->count();
                $total_alpha = $this->data->whereIn('status', ['Alpa', 'Tanpa Keterangan'])->count();
                $total_data = $this->data->count();

                // Statistics table
                $sheet->setCellValue('A11', 'Total Kehadiran');
                $sheet->setCellValue('C11', $total_hadir);
                $sheet->setCellValue('D11', 'Persentase');
                $sheet->setCellValue('E11', $total_data > 0 ? round(($total_hadir / $total_data) * 100, 1) . '%' : '0%');

                $sheet->setCellValue('G11', 'Total Sakit');
                $sheet->setCellValue('I11', $total_sakit);
                $sheet->setCellValue('J11', 'Persentase');
                $sheet->setCellValue('K11', $total_data > 0 ? round(($total_sakit / $total_data) * 100, 1) . '%' : '0%');

                $sheet->setCellValue('A12', 'Total Izin');
                $sheet->setCellValue('C12', $total_izin);
                $sheet->setCellValue('D12', 'Persentase');
                $sheet->setCellValue('E12', $total_data > 0 ? round(($total_izin / $total_data) * 100, 1) . '%' : '0%');

                $sheet->setCellValue('G12', 'Tanpa Keterangan');
                $sheet->setCellValue('I12', $total_alpha);
                $sheet->setCellValue('J12', 'Persentase');
                $sheet->setCellValue('K12', $total_data > 0 ? round(($total_alpha / $total_data) * 100, 1) . '%' : '0%');

                // Style statistics table
                $sheet->getStyle('A11:E12')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB']
                    ]
                ]);

                $sheet->getStyle('G11:K12')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB']
                    ]
                ]);

                // Center align statistic values
                $sheet->getStyle('C11:C12')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                $sheet->getStyle('E11:E12')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);
                $sheet->getStyle('I11:I12')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                $sheet->getStyle('K11:K12')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);

                // Add empty row after statistics
                $sheet->getRowDimension(13)->setRowHeight(5);

                // 4. DATA PRESENSI DETAIL
                $sheet->setCellValue('A14', '📋 Data Presensi Detail');
                $sheet->mergeCells('A14:K14');
                $sheet->getStyle('A14')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E40AF']]
                ]);

                // Add empty row for spacing
                $sheet->getRowDimension(15)->setRowHeight(10);

                // Table headers - Update label
                $row = 16;
                $sheet->setCellValue('A' . $row, 'No');
                $sheet->setCellValue('B' . $row, 'Kelas');
                $sheet->setCellValue('C' . $row, 'NIS');
                $sheet->setCellValue('D' . $row, 'Nama Siswa');

                // Always use "Jumlah hari/semester" as per requirement
                $periodLabel = 'Jumlah hari/semester';
                $sheet->setCellValue('E' . $row, $periodLabel);

                $sheet->setCellValue('F' . $row, 'Jumlah Hadir');
                $sheet->setCellValue('G' . $row, 'Jumlah Ke-tidak Hadiran');
                $sheet->setCellValue('J' . $row, 'Jumlah Total');
                $sheet->setCellValue('K' . $row, 'Keterangan');

                // Merge cells for "Jumlah Ke-tidak Hadiran"
                $sheet->mergeCells('G' . $row . ':I' . $row);

                // Center the merged cell text
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                // Sub headers for S, I, A
                $row++;
                $sheet->setCellValue('G' . $row, 'S');
                $sheet->setCellValue('H' . $row, 'I');
                $sheet->setCellValue('I' . $row, 'A');

                // Style headers
                $sheet->getStyle('A16:K17')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E40AF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E3A8A'],
                        ],
                        'inside' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '3B82F6'],
                        ],
                    ],
                ]);

                // Merge cells for other headers that span 2 rows
                $sheet->mergeCells('A16:A17');
                $sheet->mergeCells('B16:B17');
                $sheet->mergeCells('C16:C17');
                $sheet->mergeCells('D16:D17');
                $sheet->mergeCells('E16:E17');
                $sheet->mergeCells('F16:F17');
                $sheet->mergeCells('J16:J17');
                $sheet->mergeCells('K16:K17');

                // Set row heights
                $sheet->getRowDimension(16)->setRowHeight(30);
                $sheet->getRowDimension(17)->setRowHeight(25);

                // Fill data
                $row = 18;
                $no = 1;
                foreach ($this->groupedData as $item) {
                    $sheet->setCellValue('A' . $row, $no);
                    $sheet->setCellValue('B' . $row, $item['kelas']->nama_kelas ?? '-');
                    $sheet->setCellValue('C' . $row, $item['siswa']->nis ?? '-');
                    $sheet->setCellValue('D' . $row, $item['siswa']->nama_lengkap ?? '-');
                    $sheet->setCellValue('E' . $row, $item['jumlah_hari']);
                    $sheet->setCellValue('F' . $row, $item['jumlah_hadir']);
                    $sheet->setCellValue('G' . $row, $item['jumlah_sakit']);
                    $sheet->setCellValue('H' . $row, $item['jumlah_izin']);
                    $sheet->setCellValue('I' . $row, $item['jumlah_alpha']);
                    $sheet->setCellValue('J' . $row, $item['jumlah_total']);
                    $sheet->setCellValue('K' . $row, $item['keterangan']);

                    // Apply zebra striping
                    if ($no % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F9FAFB']
                            ],
                        ]);
                    }

                    // Set font size for data rows
                    $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                        'font' => ['size' => 10]
                    ]);

                    $no++;
                    $row++;
                }

                // Apply borders to data rows
                $sheet->getStyle('A18:K' . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // Center align numeric columns and No column
                $sheet->getStyle('A18:A' . ($row - 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                $sheet->getStyle('E18:J' . ($row - 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Center align Kelas and Keterangan columns
                $sheet->getStyle('B18:B' . ($row - 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                $sheet->getStyle('K18:K' . ($row - 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Center align NIS column
                $sheet->getStyle('C18:C' . ($row - 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Set specific column widths
                $sheet->getColumnDimension('A')->setWidth(4);   // No
                $sheet->getColumnDimension('B')->setWidth(20);  // Kelas
                $sheet->getColumnDimension('C')->setWidth(12);   // NIS
                $sheet->getColumnDimension('D')->setWidth(25);  // Nama Siswa
                $sheet->getColumnDimension('E')->setWidth(18);  // Jumlah hari/periode
                $sheet->getColumnDimension('F')->setWidth(12);  // Jumlah Hadir
                $sheet->getColumnDimension('G')->setWidth(8);   // S
                $sheet->getColumnDimension('H')->setWidth(8);   // I
                $sheet->getColumnDimension('I')->setWidth(8);   // A
                $sheet->getColumnDimension('J')->setWidth(11);  // Jumlah Total
                $sheet->getColumnDimension('K')->setWidth(18);  // Keterangan

                // 5. FORMULA DAN TANDA TANGAN (Skip untuk Wali Murid)
                if (!$this->is_wali_murid) {
                    $row += 2;
                    $sheet->setCellValue('A' . $row, 'Keterangan:');
                    $sheet->getStyle('A' . $row)->applyFromArray([
                        'font' => ['bold' => true]
                    ]);

                    $row++;
                    $periodText = $this->periode_type === 'semester' ? 'semester ini' : 'bulan ini';
                    $sheet->setCellValue('A' . $row, "% Absen rata-rata $periodText = (Jumlah siswa dalam $periodText / Jumlah siswa x hari masuk) x 100%");

                    // Calculate formula
                    $totalSiswa = $this->groupedData->count();
                    $totalAbsences = $total_izin + $total_sakit + $total_alpha;
                    $maxAttendances = $totalSiswa * $this->total_school_days;
                    $absentPercentage = ($maxAttendances > 0) ? ($totalAbsences / $maxAttendances) * 100 : 0;

                    $row++;
                    $sheet->setCellValue('A' . $row, "= ($totalAbsences / ($totalSiswa x {$this->total_school_days})) x 100% = " . number_format($absentPercentage, 1) . '%');

                    // 6. SIGNATURE SECTION
                    $row += 3;

                    // Check if we have specific class or multiple classes
                    if ($this->kelas && $this->wali_kelas) {
                        // Single class with wali kelas
                        $sheet->setCellValue('B' . $row, 'Mengetahui,');
                        $sheet->setCellValue('H' . $row, config('app.school_city', 'Banjarejo') . ', ' . Carbon::now()->translatedFormat('l, d F Y'));

                        $row++;
                        $sheet->setCellValue('B' . $row, 'Kepala Sekolah');
                        $sheet->setCellValue('H' . $row, 'Wali ' . $this->kelas->nama_kelas);

                        $row += 4;
                        $sheet->setCellValue('B' . $row, $this->getKepalaSekolahName());
                        $sheet->setCellValue('H' . $row, $this->getWaliKelasName());

                        $row++;
                        $sheet->setCellValue('B' . $row, 'NIP. ' . $this->getKepalaSekolahNip());
                        $sheet->setCellValue('H' . $row, 'NIP. ' . $this->getWaliKelasNip());

                        // Style signature names
                        $sheet->getStyle('B' . ($row - 1))->applyFromArray([
                            'font' => ['bold' => true, 'underline' => true]
                        ]);
                        $sheet->getStyle('H' . ($row - 1))->applyFromArray([
                            'font' => ['bold' => true, 'underline' => true]
                        ]);
                    } elseif (!$this->kelas && $this->all_wali_kelas->count() > 0) {
                        // Multiple classes - show kepala sekolah only, then list all wali kelas
                        $sheet->setCellValue('F' . $row, config('app.school_city', 'Banjarejo') . ', ' . Carbon::now()->format('d F Y'));
                        $sheet->mergeCells('F' . $row . ':I' . $row);
                        $sheet->getStyle('F' . $row)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);

                        $row++;
                        $sheet->setCellValue('F' . $row, 'Mengetahui,');
                        $sheet->mergeCells('F' . $row . ':I' . $row);
                        $sheet->getStyle('F' . $row)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);

                        $row++;
                        $sheet->setCellValue('F' . $row, 'Kepala Satuan Pendidikan');
                        $sheet->mergeCells('F' . $row . ':I' . $row);
                        $sheet->getStyle('F' . $row)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                        $row++;
                        $sheet->setCellValue('F' . $row, 'SDN Banjarejo');
                        $sheet->mergeCells('F' . $row . ':I' . $row);
                        $sheet->getStyle('F' . $row)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);

                        $row += 4;
                        $sheet->setCellValue('F' . $row, $this->getKepalaSekolahName());
                        $sheet->mergeCells('F' . $row . ':I' . $row);
                        $sheet->getStyle('F' . $row)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                            'font' => ['bold' => true, 'underline' => true]
                        ]);

                        $row++;
                        $sheet->setCellValue('F' . $row,  $this->getKepalaSekolahPangkat() . ' (' . $this->getKepalaSekolahGolongan(). ')');
                        $sheet->mergeCells('F' . $row . ':I' . $row);
                        $sheet->getStyle('F' . $row)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                        $row++;
                        $sheet->setCellValue('F' . $row, 'NIP. ' . $this->getKepalaSekolahNip());
                        $sheet->mergeCells('F' . $row . ':I' . $row);
                        $sheet->getStyle('F' . $row)->applyFromArray([
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                        ]);
                    } else {
                        // Fallback - no specific wali kelas information
                        $sheet->setCellValue('B' . $row, 'Mengetahui,');
                        $sheet->setCellValue('H' . $row, config('app.school_city', 'Banjarejo') . ', ' . Carbon::now()->format('d F Y'));

                        $row++;
                        $sheet->setCellValue('B' . $row, 'Kepala Sekolah');
                        $sheet->setCellValue('H' . $row, 'Wali Kelas');

                        $row += 4;
                        $sheet->setCellValue('B' . $row, $this->getKepalaSekolahName());
                        $sheet->setCellValue('H' . $row, '________________________');

                        $row++;
                        $sheet->setCellValue('B' . $row, 'NIP. ' . $this->getKepalaSekolahNip());
                        $sheet->setCellValue('H' . $row, 'NIP. ________________________');

                        // Style signature names
                        $sheet->getStyle('B' . ($row - 1))->applyFromArray([
                            'font' => ['bold' => true, 'underline' => true]
                        ]);
                    }
                }

                // Footer
                $row += 5;
                $sheet->setCellValue('A' . $row, 'Keterangan: S = Sakit | I = Izin | A = Alpha (Tanpa Keterangan)');
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '64748B']]
                ]);

                // Print settings
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setHorizontalCentered(true);

                // Set print margins
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setBottom(0.5);

                // Freeze panes at row 18 (after headers)
                $sheet->freezePane('A18');

                // Set print area
                $sheet->getPageSetup()->setPrintArea('A1:K' . ($row + 15));
            },
        ];
    }
}
