<?php

namespace App\Http\Controllers;

use App\Exports\RekapPresensiExport;
use App\Exports\RekapWaliKelasExport;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\User;
use App\Models\WaliKelas;
use App\Models\KepalaSekolah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export untuk Kepala Sekolah - Excel
     */
    public function exportKepalaSekolah(Request $request)
    {
        // Validate request parameters
        $tanggal_mulai = $request->input('tanggal_mulai', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggal_selesai = $request->input('tanggal_selesai', Carbon::now()->format('Y-m-d'));
        $kelas_id = $request->input('kelas_id');
        $periode_type = $request->input('periode_type', 'bulan');

        // Get data
        $query = Presensi::with(['siswa', 'kelas'])
            ->whereBetween('tanggal_presensi', [$tanggal_mulai, $tanggal_selesai]);

        if ($kelas_id) {
            $query->where('kelas_id', $kelas_id);
            $kelas = Kelas::find($kelas_id);
        } else {
            $kelas = null;
        }

        $data = $query->get();

        // Find wali kelas dengan logika yang sama seperti PDF export
        $wali_kelas = null;
        $all_wali_kelas = collect();

        if ($kelas_id) {
            // Jika export untuk kelas tertentu
            $wali_kelas = WaliKelas::with('user')
                ->where('kelas_id', $kelas_id)
                ->where('is_active', true)
                ->first();
        } else {
            // Jika export semua kelas, ambil semua wali kelas yang terlibat
            $kelas_ids = $data->pluck('kelas_id')->unique();
            $all_wali_kelas = WaliKelas::with(['user', 'kelas'])
                ->whereIn('kelas_id', $kelas_ids)
                ->where('is_active', true)
                ->get();

            // Jika hanya ada 1 kelas dalam data, ambil wali kelasnya
            if ($kelas_ids->count() == 1) {
                $wali_kelas = $all_wali_kelas->first();
                $kelas = Kelas::find($kelas_ids->first());
            }
        }

        // Find kepala sekolah
        $kepala_sekolah = KepalaSekolah::with('user')
            ->where('is_active', true)
            ->first();

        // Generate file name
        $fileName = 'rekap_presensi_';
        $fileName .= $kelas ? $kelas->nama_kelas . '_' : 'semua_kelas_';
        $fileName .= $periode_type === 'semester' ? 'semester_' : '';
        $fileName .= Carbon::parse($tanggal_mulai)->format('d-m-Y') . '_sd_' . Carbon::parse($tanggal_selesai)->format('d-m-Y');
        $fileName .= '.xlsx';

        // Return Excel file dengan data kepala sekolah dan wali kelas
        return Excel::download(
            new RekapWaliKelasExport($data, $tanggal_mulai, $tanggal_selesai, $kelas, $wali_kelas, $kepala_sekolah, $periode_type),
            $fileName
        );
    }

    // Export to PDF for Kepala Sekolah
    public function exportKepalaSekolahPDF(Request $request)
    {
        // Validate request parameters
        $tanggal_mulai = $request->input('tanggal_mulai', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggal_selesai = $request->input('tanggal_selesai', Carbon::now()->format('Y-m-d'));
        $kelas_id = $request->input('kelas_id');
        $periode_type = $request->input('periode_type', 'bulan');

        // Get data
        $query = Presensi::with(['siswa', 'kelas'])
            ->whereBetween('tanggal_presensi', [$tanggal_mulai, $tanggal_selesai]);

        if ($kelas_id) {
            $query->where('kelas_id', $kelas_id);
            $kelas = Kelas::find($kelas_id);
        } else {
            $kelas = null;
        }

        $data = $query->get();

        // Process the data for grouping by student
        $groupedData = $data->groupBy('siswa_id')->map(function ($studentData) use ($tanggal_mulai, $tanggal_selesai) {
            $presensi = $studentData->first(); // Get first presensi record

            // Calculate total school days
            $totalSchoolDays = $this->calculateSchoolDays($tanggal_mulai, $tanggal_selesai);

            $jumlah_hadir = $studentData->where('status', 'Hadir')->count();
            $jumlah_sakit = $studentData->where('status', 'Sakit')->count();
            $jumlah_izin = $studentData->where('status', 'Izin')->count();
            $jumlah_alpha = $studentData->where('status', 'Alpa')->count();

            $percentage = $totalSchoolDays > 0 ? ($jumlah_hadir / $totalSchoolDays) * 100 : 0;
            $keterangan = 'Sangat Kurang';
            if ($percentage >= 90) $keterangan = 'Baik';
            elseif ($percentage >= 80) $keterangan = 'Cukup';
            elseif ($percentage >= 70) $keterangan = 'Kurang';

            return [
                'presensi' => $presensi,
                'siswa' => $presensi->siswa, // Access through relationship
                'kelas' => $presensi->kelas, // Access through relationship
                'jumlah_hari' => $totalSchoolDays,
                'jumlah_hadir' => $jumlah_hadir,
                'jumlah_sakit' => $jumlah_sakit,
                'jumlah_izin' => $jumlah_izin,
                'jumlah_alpha' => $jumlah_alpha,
                'jumlah_total' => $jumlah_sakit + $jumlah_izin + $jumlah_alpha,
                'keterangan' => $keterangan
            ];
        })->values();

        // Find wali kelas (class teacher) for the current class
        $wali_kelas = null;
        $all_wali_kelas = collect(); // Untuk menyimpan semua wali kelas jika export semua kelas

        if ($kelas_id) {
            // Jika export untuk kelas tertentu
            $wali_kelas = WaliKelas::with('user')
                ->where('kelas_id', $kelas_id)
                ->where('is_active', true)
                ->first();
        } else {
            // Jika export semua kelas, ambil semua wali kelas yang terlibat
            $kelas_ids = $data->pluck('kelas_id')->unique();
            $all_wali_kelas = WaliKelas::with(['user', 'kelas'])
                ->whereIn('kelas_id', $kelas_ids)
                ->where('is_active', true)
                ->get();

            // Jika hanya ada 1 kelas dalam data, ambil wali kelasnya
            if ($kelas_ids->count() == 1) {
                $wali_kelas = $all_wali_kelas->first();
                $kelas = Kelas::find($kelas_ids->first());
            }
        }

        // Find kepala sekolah (school principal)
        $kepala_sekolah = KepalaSekolah::with('user')
            ->where('is_active', true)
            ->first();

        // Generate file name
        $fileName = 'rekap_presensi_';
        $fileName .= $kelas ? $kelas->nama_kelas . '_' : 'semua_kelas_';
        $fileName .= $periode_type === 'semester' ? 'semester_' : '';
        $fileName .= Carbon::parse($tanggal_mulai)->format('d-m-Y') . '_sd_' . Carbon::parse($tanggal_selesai)->format('d-m-Y');
        $fileName .= '.pdf';

        // Get the PDF view
        $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
            'data' => $data,
            'groupedData' => $groupedData,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'kelas' => $kelas,
            'exported_at' => Carbon::now(),
            'wali_kelas' => $wali_kelas,
            'all_wali_kelas' => $all_wali_kelas, // Kirim semua wali kelas jika export semua kelas
            'kepala_sekolah' => $kepala_sekolah,
            'periode_type' => $periode_type,
        ]);

        // Set paper to landscape for better table viewing
        $pdf->setPaper('a4', 'landscape');

        // Return PDF file
        return $pdf->download($fileName);
    }

    /**
     * Export untuk Wali Kelas
     */
    public function exportWaliKelas(Request $request)
    {
        try {
            // Pastikan user adalah wali kelas
            if (!auth()->user()->hasRole('Wali Kelas') && !auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('Admin')) {
                abort(403, 'Akses ditolak');
            }

            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ]);

            $periode_type = $request->input('periode_type', 'bulan');

            $waliKelas = auth()->user()->waliKelas;
            if (!$waliKelas) {
                abort(404, 'Data wali kelas tidak ditemukan');
            }

            // Ambil data presensi hanya untuk kelas yang dipegang
            $data = Presensi::with(['siswa', 'kelas'])
                ->where('kelas_id', $waliKelas->kelas_id)
                ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai])
                ->orderBy('tanggal_presensi', 'desc')
                ->orderBy('siswa_id')
                ->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Tidak ada data presensi untuk periode yang dipilih.');
            }

            $kelas = $waliKelas->kelas;

            // Find kepala sekolah
            $kepala_sekolah = KepalaSekolah::with('user')
                ->where('is_active', true)
                ->first();

            $filename = 'rekap-presensi-' . str_replace(' ', '-', strtolower($kelas->nama_kelas)) . '-' .
                ($periode_type === 'semester' ? 'semester-' : '') .
                Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
                Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.xlsx';

            return Excel::download(
                new RekapWaliKelasExport($data, $request->tanggal_mulai, $request->tanggal_selesai, $kelas, $waliKelas, $kepala_sekolah, $periode_type),
                $filename
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Export untuk Wali Murid
     */
    public function exportWaliMurid(Request $request)
    {
        try {
            // Pastikan user adalah wali murid
            if (!auth()->user()->hasRole('Wali Murid') && !auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('Admin')) {
                abort(403, 'Akses ditolak');
            }

            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ]);

            $periode_type = $request->input('periode_type', 'bulan');

            $waliMurid = auth()->user()->waliMurid;
            if (!$waliMurid) {
                abort(404, 'Data wali murid tidak ditemukan');
            }

            // Ambil data presensi hanya untuk siswa yang bersangkutan
            $data = Presensi::with(['siswa', 'kelas'])
                ->where('siswa_id', $waliMurid->siswa_id)
                ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai])
                ->orderBy('tanggal_presensi', 'desc')
                ->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Tidak ada data presensi untuk periode yang dipilih.');
            }

            $siswa = $waliMurid->siswa;
            $kelas = $siswa->kelas;

            // Find wali kelas untuk kelas ini
            $wali_kelas = WaliKelas::with('user')
                ->where('kelas_id', $kelas->id)
                ->where('is_active', true)
                ->first();

            // Find kepala sekolah
            $kepala_sekolah = KepalaSekolah::with('user')
                ->where('is_active', true)
                ->first();

            $filename = 'rekap-presensi-' . str_replace(' ', '-', strtolower($siswa->nama_lengkap)) . '-' .
                ($periode_type === 'semester' ? 'semester-' : '') .
                Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
                Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.xlsx';

            return Excel::download(
                new RekapWaliKelasExport($data, $request->tanggal_mulai, $request->tanggal_selesai, $kelas, $wali_kelas, $kepala_sekolah, $periode_type),
                $filename
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Export umum untuk admin
     */
    public function exportGeneral(Request $request)
    {
        try {
            // Hanya admin yang bisa akses
            if (!auth()->user()->hasRole('super_admin')) {
                abort(403, 'Akses ditolak');
            }

            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'kelas_id' => 'nullable|exists:kelas,id',
                'status' => 'nullable|in:Hadir,Izin,Sakit,Alpa',
            ]);

            $periode_type = $request->input('periode_type', 'bulan');

            $query = Presensi::with(['siswa', 'kelas'])
                ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai]);

            if ($request->kelas_id) {
                $query->where('kelas_id', $request->kelas_id);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $data = $query->orderBy('tanggal_presensi', 'desc')
                ->orderBy('kelas_id')
                ->orderBy('siswa_id')
                ->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Tidak ada data presensi untuk periode yang dipilih.');
            }

            $kelas = $request->kelas_id ? Kelas::find($request->kelas_id) : null;

            // Find wali kelas dengan logika yang sama seperti exportKepalaSekolah
            $wali_kelas = null;
            $all_wali_kelas = collect();

            if ($request->kelas_id) {
                // Jika export untuk kelas tertentu
                $wali_kelas = WaliKelas::with('user')
                    ->where('kelas_id', $request->kelas_id)
                    ->where('is_active', true)
                    ->first();
            } else {
                // Jika export semua kelas, ambil semua wali kelas yang terlibat
                $kelas_ids = $data->pluck('kelas_id')->unique();
                $all_wali_kelas = WaliKelas::with(['user', 'kelas'])
                    ->whereIn('kelas_id', $kelas_ids)
                    ->where('is_active', true)
                    ->get();

                // Jika hanya ada 1 kelas dalam data, ambil wali kelasnya
                if ($kelas_ids->count() == 1) {
                    $wali_kelas = $all_wali_kelas->first();
                    $kelas = Kelas::find($kelas_ids->first());
                }
            }

            // Find kepala sekolah
            $kepala_sekolah = KepalaSekolah::with('user')
                ->where('is_active', true)
                ->first();

            $filename = 'rekap-presensi-general-' .
                Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
                Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.xlsx';

            return Excel::download(
                new RekapWaliKelasExport($data, $request->tanggal_mulai, $request->tanggal_selesai, $kelas, $wali_kelas, $kepala_sekolah, $periode_type),
                $filename
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage());
        }
    }

    /**
     * Export PDF untuk Wali Kelas
     */
    public function exportWaliKelasPdf(Request $request)
    {
        try {
            if (!auth()->user()->hasRole('Wali Kelas') && !auth()->user()->hasRole('super_admin')) {
                abort(403, 'Akses ditolak');
            }

            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ]);

            $periode_type = $request->input('periode_type', 'bulan');

            $waliKelas = auth()->user()->waliKelas;
            if (!$waliKelas) {
                abort(404, 'Data wali kelas tidak ditemukan');
            }

            $data = Presensi::with(['siswa', 'kelas'])
                ->where('kelas_id', $waliKelas->kelas_id)
                ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai])
                ->orderBy('tanggal_presensi', 'desc')
                ->orderBy('siswa_id')
                ->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Tidak ada data presensi untuk periode yang dipilih.');
            }

            $kelas = $waliKelas->kelas;

            // Find kepala sekolah (school principal)
            $kepala_sekolah = KepalaSekolah::with('user')
                ->where('is_active', true)
                ->first();

            // Process data untuk grouping
            $groupedData = $data->groupBy('siswa_id')->map(function ($studentData) use ($request) {
                $presensi = $studentData->first();

                // Calculate total school days
                $totalSchoolDays = $this->calculateSchoolDays($request->tanggal_mulai, $request->tanggal_selesai);

                $jumlah_hadir = $studentData->where('status', 'Hadir')->count();
                $jumlah_sakit = $studentData->where('status', 'Sakit')->count();
                $jumlah_izin = $studentData->where('status', 'Izin')->count();
                $jumlah_alpha = $studentData->where('status', 'Alpa')->count();

                $percentage = $totalSchoolDays > 0 ? ($jumlah_hadir / $totalSchoolDays) * 100 : 0;
                $keterangan = 'Sangat Kurang';
                if ($percentage >= 90) $keterangan = 'Baik';
                elseif ($percentage >= 80) $keterangan = 'Cukup';
                elseif ($percentage >= 70) $keterangan = 'Kurang';

                return [
                    'presensi' => $presensi,
                    'siswa' => $presensi->siswa,
                    'kelas' => $presensi->kelas,
                    'jumlah_hari' => $totalSchoolDays,
                    'jumlah_hadir' => $jumlah_hadir,
                    'jumlah_sakit' => $jumlah_sakit,
                    'jumlah_izin' => $jumlah_izin,
                    'jumlah_alpha' => $jumlah_alpha,
                    'jumlah_total' => $jumlah_sakit + $jumlah_izin + $jumlah_alpha,
                    'keterangan' => $keterangan
                ];
            })->values();

            $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
                'data' => $data,
                'groupedData' => $groupedData,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'kelas' => $kelas,
                'wali_kelas' => $waliKelas,
                'kepala_sekolah' => $kepala_sekolah,
                'periode_type' => $periode_type,
            ]);

            $pdf->setPaper('A4', 'landscape');

            $filename = 'rekap-presensi-' . str_replace(' ', '-', strtolower($kelas->nama_kelas)) . '-' .
                ($periode_type === 'semester' ? 'semester-' : '') .
                Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
                Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export PDF untuk Wali Murid
     */
    public function exportWaliMuridPdf(Request $request)
    {
        try {
            if (!auth()->user()->hasRole('Wali Murid') && !auth()->user()->hasRole('super_admin')) {
                abort(403, 'Akses ditolak');
            }

            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            ]);

            $periode_type = $request->input('periode_type', 'bulan');

            $waliMurid = auth()->user()->waliMurid;
            if (!$waliMurid) {
                abort(404, 'Data wali murid tidak ditemukan');
            }

            $data = Presensi::with(['siswa', 'kelas'])
                ->where('siswa_id', $waliMurid->siswa_id)
                ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai])
                ->orderBy('tanggal_presensi', 'desc')
                ->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Tidak ada data presensi untuk periode yang dipilih.');
            }

            $siswa = $waliMurid->siswa;
            $kelas = $siswa->kelas;

            // Find wali kelas for this class
            $waliKelas = WaliKelas::with('user')
                ->where('kelas_id', $kelas->id)
                ->where('is_active', true)
                ->first();

            // Find kepala sekolah (school principal)
            $kepala_sekolah = KepalaSekolah::with('user')
                ->where('is_active', true)
                ->first();

            // Process data untuk grouping
            $groupedData = $data->groupBy('siswa_id')->map(function ($studentData) use ($request) {
                $presensi = $studentData->first();

                // Calculate total school days
                $totalSchoolDays = $this->calculateSchoolDays($request->tanggal_mulai, $request->tanggal_selesai);

                $jumlah_hadir = $studentData->where('status', 'Hadir')->count();
                $jumlah_sakit = $studentData->where('status', 'Sakit')->count();
                $jumlah_izin = $studentData->where('status', 'Izin')->count();
                $jumlah_alpha = $studentData->where('status', 'Alpa')->count();

                $percentage = $totalSchoolDays > 0 ? ($jumlah_hadir / $totalSchoolDays) * 100 : 0;
                $keterangan = 'Sangat Kurang';
                if ($percentage >= 90) $keterangan = 'Baik';
                elseif ($percentage >= 80) $keterangan = 'Cukup';
                elseif ($percentage >= 70) $keterangan = 'Kurang';

                return [
                    'presensi' => $presensi,
                    'siswa' => $presensi->siswa,
                    'kelas' => $presensi->kelas,
                    'jumlah_hari' => $totalSchoolDays,
                    'jumlah_hadir' => $jumlah_hadir,
                    'jumlah_sakit' => $jumlah_sakit,
                    'jumlah_izin' => $jumlah_izin,
                    'jumlah_alpha' => $jumlah_alpha,
                    'jumlah_total' => $jumlah_sakit + $jumlah_izin + $jumlah_alpha,
                    'keterangan' => $keterangan
                ];
            })->values();

            $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
                'data' => $data,
                'groupedData' => $groupedData,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'kelas' => $kelas,
                'wali_kelas' => $waliKelas,
                'kepala_sekolah' => $kepala_sekolah,
                'periode_type' => $periode_type,
            ]);

            $pdf->setPaper('A4', 'landscape');

            $filename = 'rekap-presensi-' . str_replace(' ', '-', strtolower($siswa->nama_lengkap)) . '-' .
                ($periode_type === 'semester' ? 'semester-' : '') .
                Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
                Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export PDF untuk Admin/General
     */
    public function exportGeneralPdf(Request $request)
    {
        try {
            // Hanya admin yang bisa akses
            if (!auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('Kepala Sekolah')) {
                abort(403, 'Akses ditolak');
            }

            $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'kelas_id' => 'nullable|exists:kelas,id',
                'status' => 'nullable|in:Hadir,Izin,Sakit,Alpa',
            ]);

            $periode_type = $request->input('periode_type', 'bulan');

            $query = Presensi::with(['siswa', 'kelas'])
                ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai]);

            if ($request->kelas_id) {
                $query->where('kelas_id', $request->kelas_id);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $data = $query->orderBy('tanggal_presensi', 'desc')
                ->orderBy('kelas_id')
                ->orderBy('siswa_id')
                ->get();

            if ($data->isEmpty()) {
                return back()->with('error', 'Tidak ada data presensi untuk periode yang dipilih.');
            }

            $kelas = $request->kelas_id ? Kelas::find($request->kelas_id) : null;

            // Find wali kelas untuk kelas yang dipilih (jika ada)
            $wali_kelas = null;
            $all_wali_kelas = collect();

            if ($request->kelas_id) {
                // Jika export untuk kelas tertentu
                $wali_kelas = WaliKelas::with('user')
                    ->where('kelas_id', $request->kelas_id)
                    ->where('is_active', true)
                    ->first();
            } else {
                // Jika export semua kelas, ambil semua wali kelas yang terlibat
                $kelas_ids = $data->pluck('kelas_id')->unique();
                $all_wali_kelas = WaliKelas::with(['user', 'kelas'])
                    ->whereIn('kelas_id', $kelas_ids)
                    ->where('is_active', true)
                    ->get();

                // Jika hanya ada 1 kelas dalam data, ambil wali kelasnya
                if ($kelas_ids->count() == 1) {
                    $wali_kelas = $all_wali_kelas->first();
                    $kelas = Kelas::find($kelas_ids->first());
                }
            }

            // Find kepala sekolah
            $kepala_sekolah = KepalaSekolah::with('user')
                ->where('is_active', true)
                ->first();

            // Process data untuk grouping
            $groupedData = $data->groupBy('siswa_id')->map(function ($studentData) use ($request) {
                $presensi = $studentData->first();

                // Calculate total school days
                $totalSchoolDays = $this->calculateSchoolDays($request->tanggal_mulai, $request->tanggal_selesai);

                $jumlah_hadir = $studentData->where('status', 'Hadir')->count();
                $jumlah_sakit = $studentData->where('status', 'Sakit')->count();
                $jumlah_izin = $studentData->where('status', 'Izin')->count();
                $jumlah_alpha = $studentData->where('status', 'Alpa')->count();

                $percentage = $totalSchoolDays > 0 ? ($jumlah_hadir / $totalSchoolDays) * 100 : 0;
                $keterangan = 'Sangat Kurang';
                if ($percentage >= 90) $keterangan = 'Baik';
                elseif ($percentage >= 80) $keterangan = 'Cukup';
                elseif ($percentage >= 70) $keterangan = 'Kurang';

                return [
                    'presensi' => $presensi,
                    'siswa' => $presensi->siswa,
                    'kelas' => $presensi->kelas,
                    'jumlah_hari' => $totalSchoolDays,
                    'jumlah_hadir' => $jumlah_hadir,
                    'jumlah_sakit' => $jumlah_sakit,
                    'jumlah_izin' => $jumlah_izin,
                    'jumlah_alpha' => $jumlah_alpha,
                    'jumlah_total' => $jumlah_sakit + $jumlah_izin + $jumlah_alpha,
                    'keterangan' => $keterangan
                ];
            })->values();

            $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
                'data' => $data,
                'groupedData' => $groupedData,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'kelas' => $kelas,
                'wali_kelas' => $wali_kelas,
                'all_wali_kelas' => $all_wali_kelas,
                'kepala_sekolah' => $kepala_sekolah,
                'periode_type' => $periode_type,
            ]);

            $pdf->setPaper('A4', 'landscape');

            $filename = 'rekap-presensi-general-' .
                ($kelas ? str_replace(' ', '-', strtolower($kelas->nama_kelas)) . '-' : 'semua-kelas-') .
                ($periode_type === 'semester' ? 'semester-' : '') .
                Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
                Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengekspor PDF: ' . $e->getMessage());
        }
    }

    /**
     * Calculate total school days (weekdays only)
     */
    protected function calculateSchoolDays($start, $end)
    {
        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        $schoolDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            // Count only weekdays (Monday to Friday)
            if ($current->isWeekday()) {
                $schoolDays++;
            }
            $current->addDay();
        }

        return $schoolDays;
    }
}
