<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return redirect('/admin/login');
});
Route::middleware(['auth'])->group(function () {

    // Export untuk Kepala Sekolah
    Route::get('/export/presensi/kepala-sekolah', [ExportController::class, 'exportKepalaSekolah'])
        ->name('export.presensi.kepala-sekolah');
    // ->middleware('role:Kepala Sekolah|super_admin');
    //
    // Export untuk Wali Kelas
    Route::get('/export/presensi/wali-kelas', [ExportController::class, 'exportWaliKelas'])
        ->name('export.presensi.wali-kelas');
    // ->middleware('role:Wali Kelas|super_admin');

    // Export untuk Wali Murid
    Route::get('/export/presensi/wali-murid', [ExportController::class, 'exportWaliMurid'])
        ->name('export.presensi.wali-murid');
    // ->middleware('role:Wali Murid|super_admin');

    // Export umum (untuk admin)
    Route::get('/export/presensi/general', [ExportController::class, 'exportGeneral'])
        ->name('export.presensi.general');
    // ->middleware('role:super_admin');

    // Export PDF routes
    Route::get('/export/presensi/pdf/kepala-sekolah', [ExportController::class, 'exportKepalaSekolahPdf'])
        ->name('export.presensi.pdf.kepala-sekolah');
    // ->middleware('role:Kepala Sekolah|super_admin');

    Route::get('/export/presensi/pdf/wali-kelas', [ExportController::class, 'exportWaliKelasPdf'])
        ->name('export.presensi.pdf.wali-kelas');
    // ->middleware('role:Wali Kelas|super_admin');

    Route::get('/export/presensi/pdf/wali-murid', [ExportController::class, 'exportWaliMuridPdf'])
        ->name('export.presensi.pdf.wali-murid');
    // ->middleware('role:Wali Murid|super_admin');
});
