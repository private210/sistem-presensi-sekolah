<?php

namespace App\Filament\Resources\SiswaResource\Actions;

use App\Imports\SiswaImport;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportSiswaAction
{
    public static function make(): Action
    {
        return Action::make('import')
            ->label('Import Siswa')
            ->icon('heroicon-o-document-arrow-up')
            ->color('success')
            ->form([
                Section::make('Import Data Siswa')
                    ->description('Upload file Excel atau CSV untuk mengimport data siswa secara massal')
                    ->schema([
                        Placeholder::make('info')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="space-y-4">
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">📁 Format file yang didukung:</h4>
                                        <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                                            <li>Excel (.xlsx, .xls)</li>
                                            <li>CSV (.csv)</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 mb-2">📋 Kolom yang diperlukan (Header harus sesuai):</h4>
                                        <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                                            <li><strong>NIS:</strong> Nomor Induk Siswa (harus berbeda)</li>
                                            <li><strong>Nama Lengkap:</strong> Nama lengkap siswa</li>
                                            <li><strong>Kelas:</strong> Nama kelas (contoh: Kelas 1)</li>
                                            <li><strong>Jenis Kelamin:</strong> L untuk (Laki-laki) atau P untuk (Perempuan)</li>
                                            <li><strong>Tanggal Lahir:</strong> Format: 2007-05-15</li>
                                            <li><strong>Alamat:</strong> Alamat lengkap (boleh kosong)</li>
                                        </ul>
                                    </div>
                                    <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                                        <p class="text-red-800 text-sm">
                                            ⚠️ <strong>Penting:</strong> Pastikan nama header kolom sesuai persis dengan yang diperlukan!
                                        </p>
                                    </div>
                                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                                        <p class="text-blue-800 text-sm">
                                            💡 <strong>Tips:</strong> Download template terlebih dahulu untuk memastikan format yang benar!
                                        </p>
                                    </div>
                                </div>
                            ')),
                        FileUpload::make('file')
                            ->label('File Import')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                                'application/csv',
                            ])
                            ->maxSize(2048) // 2MB
                            ->required()
                            ->disk('public')
                            ->directory('imports-siswa')
                            ->helperText('Maksimal 2MB. Pastikan format sesuai template yang disediakan.'),
                    ])
            ])
            ->action(function (array $data) {
                try {
                    DB::beginTransaction();

                    $fileName = $data['file'];
                    Log::info('Starting import process', ['fileName' => $fileName]);

                    // Cek apakah file ada di storage public
                    if (!Storage::disk('public')->exists($fileName)) {
                        throw new \Exception('File tidak ditemukan: ' . $fileName);
                    }

                    // Dapatkan full path dari file
                    $filePath = Storage::disk('public')->path($fileName);

                    // Verifikasi file exists
                    if (!file_exists($filePath)) {
                        throw new \Exception('File fisik tidak ditemukan di: ' . $filePath);
                    }

                    Log::info('File found at path: ' . $filePath);

                    // Cek ekstensi file dan tentukan reader yang tepat
                    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    Log::info('File extension: ' . $extension);

                    // Untuk CSV, pastikan encoding dan delimiter yang benar
                    if ($extension === 'csv') {
                        // Baca beberapa baris pertama untuk debug
                        $handle = fopen($filePath, 'r');
                        if ($handle) {
                            $firstLine = fgets($handle);
                            $secondLine = fgets($handle);
                            fclose($handle);

                            Log::info('CSV first line: ' . $firstLine);
                            Log::info('CSV second line: ' . $secondLine);
                        }
                    }

                    // Import data dengan konfigurasi yang lebih baik
                    $import = new SiswaImport();

                    // Gunakan konfigurasi khusus untuk CSV
                    if ($extension === 'csv') {
                        Excel::import($import, $filePath, null, \Maatwebsite\Excel\Excel::CSV);
                    } else {
                        Excel::import($import, $filePath);
                    }

                    // Hapus file setelah import berhasil
                    Storage::disk('public')->delete($fileName);

                    // Dapatkan hasil import
                    $imported = $import->getImportedCount();
                    $errors = $import->getErrors();
                    $skipped = $import->getSkippedCount();

                    DB::commit();

                    Log::info('Import completed successfully', [
                        'imported' => $imported,
                        'skipped' => $skipped,
                        'errors' => count($errors)
                    ]);

                    // Notifikasi hasil
                    $message = "Import selesai!\n";
                    $message .= "✅ Berhasil: {$imported} data\n";
                    if ($skipped > 0) {
                        $message .= "⏭️ Dilewati: {$skipped} data\n";
                    }
                    if (!empty($errors)) {
                        $message .= "❌ Error: " . count($errors) . " data\n";
                        $message .= "\nDetail error:\n" . implode("\n", array_slice($errors, 0, 3));
                        if (count($errors) > 3) {
                            $message .= "\n... dan " . (count($errors) - 3) . " error lainnya";
                        }
                    }

                    if ($imported > 0) {
                        Notification::make()
                            ->title('Import Berhasil')
                            ->body($message)
                            ->success()
                            ->duration(15000)
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Import Selesai')
                            ->body($message)
                            ->warning()
                            ->duration(15000)
                            ->send();
                    }
                } catch (\Exception $e) {
                    DB::rollback();

                    Log::error('Import failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Hapus file jika ada error
                    if (isset($data['file'])) {
                        try {
                            Storage::disk('public')->delete($data['file']);
                        } catch (\Exception $deleteError) {
                            Log::warning('Failed to delete file after error', ['error' => $deleteError->getMessage()]);
                        }
                    }

                    Notification::make()
                        ->title('Error Import')
                        ->body('Terjadi kesalahan: ' . $e->getMessage())
                        ->danger()
                        ->duration(15000)
                        ->send();
                }
            })
            ->modalSubmitActionLabel('Import Data')
            ->modalCancelActionLabel('Batal')
            ->modalWidth('lg');
    }

    public static function downloadTemplate(): Action
    {
        return Action::make('downloadTemplate')
            ->label('Download Template')
            ->icon('heroicon-o-document-arrow-down')
            ->color('info')
            ->action(function () {
                // Data template dengan header yang tepat (tanpa kutip)
                $templateData = [
                    ['NIS', 'Nama Lengkap', 'Kelas', 'Jenis Kelamin', 'Tanggal Lahir', 'Alamat'],
                    ['12345', 'Ahmad Fauzi', 'Kelas 1', 'L', '2007-05-15', 'Jl. Merdeka No. 123, Jakarta'],
                    ['12346', 'Siti Nurhaliza', 'Kelas 2', 'P', '2007-08-20', 'Jl. Sudirman No. 456, Jakarta'],
                    ['12347', 'Muhammad Rizki', 'Kelas 3', 'L', '2007-03-10', 'Jl. Diponegoro No. 789, Jakarta'],
                ];

                // Buat file temporary
                $fileName = 'template_import_siswa_' . date('Y-m-d_H-i-s') . '.csv';
                $tempPath = sys_get_temp_dir() . '/' . $fileName;

                // Buat file CSV dengan encoding UTF-8
                $handle = fopen($tempPath, 'w');

                // Tambahkan BOM untuk UTF-8 agar Excel bisa baca dengan benar
                fwrite($handle, "\xEF\xBB\xBF");

                foreach ($templateData as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);

                // Download file
                return response()->download($tempPath, $fileName, [
                    'Content-Type' => 'text/csv; charset=utf-8',
                ])->deleteFileAfterSend();
            });
    }

    /**
     * Action untuk preview data import sebelum di-import
     */
    public static function previewImport(): Action
    {
        return Action::make('previewImport')
            ->label('Preview Import')
            ->icon('heroicon-o-eye')
            ->color('warning')
            ->form([
                Section::make('Preview Data Import')
                    ->description('Upload file untuk melihat preview data sebelum import')
                    ->schema([
                        FileUpload::make('file')
                            ->label('File Preview')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                                'application/csv',
                            ])
                            ->maxSize(2048)
                            ->required()
                            ->disk('public')
                            ->directory('imports-siswa')
                            ->helperText('Upload file untuk melihat preview data'),
                    ])
            ])
            ->action(function (array $data) {
                try {
                    $fileName = $data['file'];

                    if (!Storage::disk('public')->exists($fileName)) {
                        throw new \Exception('File tidak ditemukan: ' . $fileName);
                    }

                    $filePath = Storage::disk('public')->path($fileName);

                    // Import dalam mode preview
                    $import = new SiswaImport(true);
                    Excel::import($import, $filePath);

                    // Hapus file setelah preview
                    Storage::disk('public')->delete($fileName);

                    $previewData = $import->getPreviewData();
                    $errors = $import->getErrors();

                    $message = "Preview Data Import:\n";
                    $message .= "Total baris: " . count($previewData) . "\n";
                    $message .= "Data valid: " . count(array_filter($previewData, fn($item) => $item['status'] === 'valid')) . "\n";

                    if (!empty($errors)) {
                        $message .= "Error: " . count($errors) . "\n";
                        $message .= "Detail error:\n" . implode("\n", array_slice($errors, 0, 5));
                    }

                    if (!empty($previewData)) {
                        $message .= "\nContoh data valid:\n";
                        foreach (array_slice($previewData, 0, 3) as $item) {
                            if ($item['status'] === 'valid') {
                                $data = $item['data'];
                                $message .= "- {$data['nis']} | {$data['nama_lengkap']} | {$data['nama_kelas']}\n";
                            }
                        }
                    }

                    Notification::make()
                        ->title('Preview Import')
                        ->body($message)
                        ->info()
                        ->duration(20000)
                        ->send();
                } catch (\Exception $e) {
                    if (isset($data['file'])) {
                        Storage::disk('public')->delete($data['file']);
                    }

                    Notification::make()
                        ->title('Error Preview')
                        ->body('Gagal preview: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalSubmitActionLabel('Preview Data')
            ->modalCancelActionLabel('Batal');
    }
}
