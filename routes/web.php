<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SiswaImportController;

Route::get('/', function () {
    return redirect('/admin/login');
});
// Route::get('/', function () {
//     return view('home');
// });
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

// Route untuk import siswa (dalam group auth dan role)
Route::middleware(['auth', 'role:Admin|Kepala Sekolah|Wali Kelas'])->group(function () {

    // Import siswa dari file
    Route::post('/siswa/import', [SiswaImportController::class, 'import'])
        ->name('siswa.import');

    // Download template import
    Route::get('/siswa/import/template', [SiswaImportController::class, 'downloadTemplate'])
        ->name('siswa.import.template');

    // Validasi data sebelum import (untuk preview)
    Route::post('/siswa/import/validate', [SiswaImportController::class, 'validateImportData'])
        ->name('siswa.import.validate');
});

// Route untuk export siswa (opsional)
Route::middleware(['auth', 'role:Admin|Kepala Sekolah|Wali Kelas'])->group(function () {
    Route::get('/siswa/export', [SiswaImportController::class, 'export'])
        ->name('siswa.export');
});
