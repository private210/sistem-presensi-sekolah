<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapWaliKelasExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    protected $data;
    protected $tanggal_mulai;
    protected $tanggal_selesai;
    protected $kelas;
    protected $stats;
    protected $groupedData;

    public function __construct($data, $tanggal_mulai, $tanggal_selesai, $kelas = null)
    {
        $this->data = $data;
        $this->tanggal_mulai = $tanggal_mulai;
        $this->tanggal_selesai = $tanggal_selesai;
        $this->kelas = $kelas;

        // Group data by student and calculate statistics
        $this->groupedData = $this->processData($data);

        // Hitung statistik
        $this->stats = [
            'total_siswa' => $data->unique('siswa_id')->count(),
            'total_presensi' => $data->count(),
            'total_kehadiran' => $data->where('status', 'Hadir')->count(),
            'total_izin' => $data->where('status', 'Izin')->count(),
            'total_sakit' => $data->where('status', 'Sakit')->count(),
            'total_alpha' => $data->where('status', 'Tanpa Keterangan')->count(),
        ];
    }

    private function processData($data)
    {
        $grouped = $data->groupBy('siswa_id')->map(function ($studentData) {
            $presensi = $studentData->first(); // Get the first presensi record for this student
            $jumlah_hari = $studentData->count();
            $jumlah_hadir = $studentData->where('status', 'Hadir')->count();
            $jumlah_sakit = $studentData->where('status', 'Sakit')->count();
            $jumlah_izin = $studentData->where('status', 'Izin')->count();
            $jumlah_alpha = $studentData->where('status', 'Tanpa Keterangan')->count();

            return [
                'presensi' => $presensi,
                'siswa' => $presensi->siswa,  // Access the siswa relationship
                'kelas' => $presensi->kelas,  // Access the kelas relationship
                'jumlah_hari' => $jumlah_hari,
                'jumlah_hadir' => $jumlah_hadir,
                'jumlah_sakit' => $jumlah_sakit,
                'jumlah_izin' => $jumlah_izin,
                'jumlah_alpha' => $jumlah_alpha,
                'jumlah_total' => $jumlah_sakit + $jumlah_izin + $jumlah_alpha,
                'keterangan' => $this->generateKeterangan($jumlah_hadir, $jumlah_hari)
            ];
        });

        return $grouped->values();
    }

    private function generateKeterangan($hadir, $total)
    {
        if ($total == 0) return 'Tidak ada data';

        $percentage = ($hadir / $total) * 100;

        if ($percentage >= 90) return 'Baik';
        if ($percentage >= 80) return 'Cukup';
        if ($percentage >= 70) return 'Kurang';
        return 'Sangat Kurang';
    }

    public function collection()
    {
        return collect($this->groupedData);
    }

    public function headings(): array
    {
        return [
            'No',
            'Kelas',
            'NIS',
            'Nama Siswa',
            'Jumlah hari/bulan',
            'Jumlah Hadir',
            'Jumlah Ke-tidak Hadiran',
            '',
            '',
            'Jumlah Total',
            'Keterangan'
        ];
    }

    public function map($row): array
    {
        static $no = 1;

        return [
            $no++,
            $row['kelas']->nama_kelas ?? '-',
            $row['siswa']->nis ?? '-',
            $row['siswa']->nama_lengkap ?? '-',
            $row['jumlah_hari'],
            $row['jumlah_hadir'],
            $row['jumlah_sakit'], // S
            $row['jumlah_izin'],  // I
            $row['jumlah_alpha'], // A
            $row['jumlah_total'],
            $row['keterangan'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '2563EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            // Style untuk sub header (S, I, A)
            2 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '2563EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            // Style untuk semua data
            'A:K' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D1D5DB'],
                    ],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 12,  // Kelas
            'C' => 12,  // NIS
            'D' => 25,  // Nama Siswa
            'E' => 15,  // Jumlah hari/bulan
            'F' => 12,  // Jumlah Hadir
            'G' => 8,   // S
            'H' => 8,   // I
            'I' => 8,   // A
            'J' => 12,  // Jumlah Total
            'K' => 15,  // Keterangan
        ];
    }

    public function title(): string
    {
        return 'Rekap Presensi';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Insert informasi di atas tabel
                $sheet->insertNewRowBefore(1, 8);

                // Header utama
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A1', 'REKAP PRESENSI SISWA');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1E40AF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Center alignment untuk kolom tertentu
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E:E')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('I:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('J:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Auto height untuk semua baris
                $sheet->getDefaultRowDimension()->setRowHeight(-1);
            },
        ];
    }
}
               