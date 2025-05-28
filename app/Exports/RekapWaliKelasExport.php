<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RekapWaliKelasExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected $data;
    protected $tanggal_mulai;
    protected $tanggal_selesai;
    protected $kelas;

    public function __construct($data, $tanggal_mulai = null, $tanggal_selesai = null, $kelas = null)
    {
        $this->data = $data;
        $this->tanggal_mulai = $tanggal_mulai;
        $this->tanggal_selesai = $tanggal_selesai;
        $this->kelas = $kelas;
    }

    public function view(): View
    {
        $stats = $this->calculateStats();

        return view('exports.rekap-presensi', [
            'data' => $this->data,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'kelas' => $this->kelas,
            'stats' => $stats,
            'exported_at' => Carbon::now()->format('d/m/Y H:i:s'),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true, 'size' => 12]],
            4 => ['font' => ['bold' => true, 'size' => 12]],
            5 => ['font' => ['bold' => true, 'size' => 12]],

            // Style the table header
            'A7:H7' => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Rekap Presensi';
    }

    private function calculateStats()
    {
        return [
            'total_kehadiran' => $this->data->where('status', 'Hadir')->count(),
            'total_izin' => $this->data->where('status', 'Izin')->count(),
            'total_sakit' => $this->data->where('status', 'Sakit')->count(),
            'total_alpha' => $this->data->where('status', 'Tanpa Keterangan')->count(),
            'total_siswa' => $this->data->unique('siswa_id')->count(),
            'total_presensi' => $this->data->count(),
        ];
    }
}
