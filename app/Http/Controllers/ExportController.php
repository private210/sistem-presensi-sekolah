<?php

namespace App\Http\Controllers;

use App\Exports\RekapPresensiExport;
use App\Exports\RekapWaliKelasExport;
use App\Models\Kelas;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export untuk Kepala Sekolah
     */
    public function exportKepalaSekolah(Request $request)
    {
        // Validasi parameter
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        // Ambil data presensi
        $query = Presensi::with(['siswa', 'kelas'])
            ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai]);

        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $data = $query->orderBy('tanggal_presensi', 'desc')
            ->orderBy('kelas_id')
            ->orderBy('siswa_id')
            ->get();

        $kelas = $request->kelas_id ? Kelas::find($request->kelas_id) : null;

        $filename = 'rekap-presensi-kepala-sekolah-' .
            Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
            Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RekapWaliKelasExport($data, $request->tanggal_mulai, $request->tanggal_selesai, $kelas),
            $filename
        );
    }

    /**
     * Export untuk Wali Kelas
     */
    public function exportWaliKelas(Request $request)
    {
        // Pastikan user adalah wali kelas
        if (!auth()->user()->hasRole('Wali Kelas') && !auth()->user()->hasRole('super_admin')&& !auth()->user()->hasRole('Admin')) {
            abort(403, 'Akses ditolak');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

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

        $kelas = $waliKelas->kelas;

        $filename = 'rekap-presensi-' . str_replace(' ', '-', strtolower($kelas->nama_kelas)) . '-' .
            Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
            Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RekapWaliKelasExport($data, $request->tanggal_mulai, $request->tanggal_selesai, $kelas),
            $filename
        );
    }

    /**
     * Export untuk Wali Murid
     */
    public function exportWaliMurid(Request $request)
    {
        // Pastikan user adalah wali murid
        if (!auth()->user()->hasRole('Wali Murid') && !auth()->user()->hasRole('super_admin')&& !auth()->user()->hasRole('Admin')) {
            abort(403, 'Akses ditolak');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

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

        $siswa = $waliMurid->siswa;
        $kelas = $siswa->kelas;

        $filename = 'rekap-presensi-' . str_replace(' ', '-', strtolower($siswa->nama_lengkap)) . '-' .
            Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
            Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RekapWaliKelasExport($data, $request->tanggal_mulai, $request->tanggal_selesai, $kelas),
            $filename
        );
    }

    /**
     * Export umum untuk admin
     */
    public function exportGeneral(Request $request)
    {
        // Hanya admin yang bisa akses
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses ditolak');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'kelas_id' => 'nullable|exists:kelas,id',
            'status' => 'nullable|in:Hadir,Izin,Sakit,Tanpa Keterangan',
        ]);

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

        $kelas = $request->kelas_id ? Kelas::find($request->kelas_id) : null;

        $filename = 'rekap-presensi-general-' .
            Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
            Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new RekapWaliKelasExport($data, $request->tanggal_mulai, $request->tanggal_selesai, $kelas),
            $filename
        );
    }

    /**
     * Export PDF untuk Kepala Sekolah
     */
    public function exportKepalaSekolahPdf(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $query = Presensi::with(['siswa', 'kelas'])
            ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai]);

        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $data = $query->orderBy('tanggal_presensi', 'desc')
            ->orderBy('kelas_id')
            ->orderBy('siswa_id')
            ->get();

        $kelas = $request->kelas_id ? Kelas::find($request->kelas_id) : null;

        $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
            'data' => $data,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'kelas' => $kelas,
        ]);

        $filename = 'rekap-presensi-kepala-sekolah-' .
            Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
            Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export PDF untuk Wali Kelas
     */
    public function exportWaliKelasPdf(Request $request)
    {
        if (!auth()->user()->hasRole('Wali Kelas') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses ditolak');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

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

        $kelas = $waliKelas->kelas;

        $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
            'data' => $data,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'kelas' => $kelas,
        ]);

        $filename = 'rekap-presensi-' . str_replace(' ', '-', strtolower($kelas->nama_kelas)) . '-' .
            Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
            Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export PDF untuk Wali Murid
     */
    public function exportWaliMuridPdf(Request $request)
    {
        if (!auth()->user()->hasRole('Wali Murid') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses ditolak');
        }

        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        $waliMurid = auth()->user()->waliMurid;
        if (!$waliMurid) {
            abort(404, 'Data wali murid tidak ditemukan');
        }

        $data = Presensi::with(['siswa', 'kelas'])
            ->where('siswa_id', $waliMurid->siswa_id)
            ->whereBetween('tanggal_presensi', [$request->tanggal_mulai, $request->tanggal_selesai])
            ->orderBy('tanggal_presensi', 'desc')
            ->get();

        $siswa = $waliMurid->siswa;
        $kelas = $siswa->kelas;

        $pdf = PDF::loadView('exports.rekap-presensi-pdf', [
            'data' => $data,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'kelas' => $kelas,
        ]);

        $filename = 'rekap-presensi-' . str_replace(' ', '-', strtolower($siswa->nama_lengkap)) . '-' .
            Carbon::parse($request->tanggal_mulai)->format('Y-m-d') . '-to-' .
            Carbon::parse($request->tanggal_selesai)->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
