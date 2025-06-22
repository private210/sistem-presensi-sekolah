<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;
use Filament\Notifications\Notification;

class SiswaImportController extends Controller
{
    /**
     * Import siswa dari file Excel/CSV
     */
    public function import(Request $request)
    {
        // Validasi file upload
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ], [
            'file.required' => 'File harus dipilih',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV',
            'file.max' => 'Ukuran file maksimal 2MB'
        ]);

        if ($validator->fails()) {
            Notification::make()
                ->title('Error Validasi')
                ->body($validator->errors()->first())
                ->danger()
                ->send();

            return back();
        }

        try {
            DB::beginTransaction();

            // Import menggunakan Laravel Excel
            $import = new SiswaImport();
            Excel::import($import, $request->file('file'));

            // Dapatkan hasil import
            $imported = $import->getImportedCount();
            $errors = $import->getErrors();
            $skipped = $import->getSkippedCount();

            DB::commit();

            // Notifikasi hasil import
            if ($imported > 0) {
                Notification::make()
                    ->title('Import Berhasil')
                    ->body("Berhasil mengimport {$imported} data siswa" .
                        ($skipped > 0 ? ", {$skipped} data dilewati" : ""))
                    ->success()
                    ->send();
            }

            // Tampilkan error jika ada
            if (!empty($errors)) {
                $errorMessage = "Terdapat " . count($errors) . " error:\n" .
                    implode("\n", array_slice($errors, 0, 5));

                Notification::make()
                    ->title('Peringatan Import')
                    ->body($errorMessage)
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            DB::rollback();

            Notification::make()
                ->title('Error Import')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }

        return redirect()->route('filament.admin.resources.siswas.index');
    }

    /**
     * Download template Excel untuk import
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_import_siswa.xlsx"',
        ];

        // Data template dengan contoh
        $templateData = [
            ['NIS', 'Nama Lengkap', 'Kelas', 'Jenis Kelamin', 'Tanggal Lahir', 'Alamat'],
            ['12345', 'Ahmad Fauzi', '10 IPA 1', 'L', '2007-05-15', 'Jl. Merdeka No. 123, Jakarta'],
            ['12346', 'Siti Nurhaliza', '10 IPA 1', 'P', '2007-08-20', 'Jl. Sudirman No. 456, Jakarta'],
        ];

        return response()->stream(function () use ($templateData) {
            $handle = fopen('php://output', 'w');

            foreach ($templateData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Validasi data sebelum import
     */
    public function validateImportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            // Preview data dari file
            $import = new SiswaImport(true); // Preview mode
            Excel::import($import, $request->file('file'));

            $previewData = $import->getPreviewData();
            $errors = $import->getErrors();

            return response()->json([
                'success' => true,
                'preview' => $previewData,
                'errors' => $errors,
                'total_rows' => count($previewData)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error membaca file: ' . $e->getMessage()
            ]);
        }
    }
}
