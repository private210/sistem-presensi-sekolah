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

    Route::get('/export/presensi/kepala-sekolah/pdf', [ExportController::class, 'exportKepalaSekolahPdf'])
        ->name('export.presensi.kepala-sekolah-pdf');
        // ->middleware('role:Kepala Sekolah|super_admin');

    // Export untuk Wali Kelas
    Route::get('/export/presensi/wali-kelas', [ExportController::class, 'exportWaliKelas'])
        ->name('export.presensi.wali-kelas');
        // ->middleware('role:Wali Kelas|super_admin|Admin');

    Route::get('/export/presensi/wali-kelas/pdf', [ExportController::class, 'exportWaliKelasPdf'])
        ->name('export.presensi.wali-kelas-pdf');
        // ->middleware('role:Wali Kelas|super_admin|Admin');
//
    // Export untuk Wali Murid
    Route::get('/export/presensi/wali-murid', [ExportController::class, 'exportWaliMurid'])
        ->name('export.presensi.wali-murid');
        // ->middleware('role:Wali Murid|super_admin|Admin');

    Route::get('/export/presensi/wali-murid/pdf', [ExportController::class, 'exportWaliMuridPdf'])
        ->name('export.presensi.wali-murid-pdf');
        // ->middleware('role:Wali Murid|super_admin|Admin');

    // Export General untuk Admin
    Route::get('/export/presensi/general', [ExportController::class, 'exportGeneral'])
        ->name('export.presensi.general');
        // ->middleware('role:super_admin');
});
