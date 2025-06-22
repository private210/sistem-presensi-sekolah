<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SiswaImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    private $importedCount = 0;
    private $skippedCount = 0;
    private $errors = [];
    private $previewMode = false;
    private $previewData = [];

    public function __construct($previewMode = false)
    {
        $this->previewMode = $previewMode;
    }

    public function collection(Collection $rows)
    {
        Log::info('Import started with ' . $rows->count() . ' rows');

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena mulai dari baris ke-2 (setelah header)

            try {
                // Log raw row data untuk debugging
                Log::info("Row {$rowNumber} raw data:", $row->toArray());

                // Skip baris kosong
                if ($this->isEmptyRow($row)) {
                    Log::info("Skipping empty row {$rowNumber}");
                    continue;
                }

                // Normalisasi data dengan mapping yang lebih fleksibel
                $data = $this->normalizeRowData($row);

                // Log normalized data
                Log::info("Row {$rowNumber} normalized data:", $data);

                // Validasi data
                $validator = $this->validateRowData($data, $rowNumber);

                if ($validator->fails()) {
                    $errorMsg = "Baris {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    $this->errors[] = $errorMsg;
                    Log::warning($errorMsg);
                    $this->skippedCount++;
                    continue;
                }

                // Jika preview mode, simpan untuk preview
                if ($this->previewMode) {
                    $this->previewData[] = [
                        'row' => $rowNumber,
                        'data' => $data,
                        'status' => 'valid'
                    ];
                    continue;
                }

                // Cek duplikasi NIS
                if (Siswa::where('nis', $data['nis'])->exists()) {
                    $errorMsg = "Baris {$rowNumber}: NIS {$data['nis']} sudah ada dalam database";
                    $this->errors[] = $errorMsg;
                    Log::warning($errorMsg);
                    $this->skippedCount++;
                    continue;
                }

                // Ambil ID kelas berdasarkan nama kelas
                $kelas = Kelas::where('nama_kelas', $data['nama_kelas'])
                    ->where('is_active', true)
                    ->first();

                if (!$kelas) {
                    $errorMsg = "Baris {$rowNumber}: Kelas '{$data['nama_kelas']}' tidak ditemukan atau tidak aktif";
                    $this->errors[] = $errorMsg;
                    Log::warning($errorMsg);
                    $this->skippedCount++;
                    continue;
                }

                // Cek akses berdasarkan role user
                if (!$this->canAccessKelas($kelas->id)) {
                    $errorMsg = "Baris {$rowNumber}: Anda tidak memiliki akses ke kelas '{$data['nama_kelas']}'";
                    $this->errors[] = $errorMsg;
                    Log::warning($errorMsg);
                    $this->skippedCount++;
                    continue;
                }

                // Simpan data siswa
                $siswa = Siswa::create([
                    'nis' => $data['nis'],
                    'nama_lengkap' => $data['nama_lengkap'],
                    'kelas_id' => $kelas->id,
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'alamat' => $data['alamat'],
                    'is_active' => true,
                ]);

                Log::info("Successfully created siswa with ID: " . $siswa->id);
                $this->importedCount++;
            } catch (\Exception $e) {
                $errorMsg = "Baris {$rowNumber}: Error - " . $e->getMessage();
                $this->errors[] = $errorMsg;
                Log::error($errorMsg, ['exception' => $e]);
                $this->skippedCount++;
            }
        }

        Log::info('Import completed', [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => count($this->errors)
        ]);
    }

    /**
     * Cek apakah baris kosong
     */
    private function isEmptyRow($row)
    {
        // Konversi ke array dan filter nilai kosong
        $values = array_filter($row->toArray(), function ($value) {
            return !empty(trim($value));
        });

        return empty($values);
    }

    /**
     * Normalisasi data dari row Excel/CSV dengan mapping yang fleksibel
     */
    private function normalizeRowData($row)
    {
        // Konversi row ke array untuk kemudahan akses
        $rowArray = $row->toArray();

        // Mapping kolom yang fleksibel (case insensitive dan berbagai variasi nama)
        $mapping = [
            'nis' => $this->findColumnValue($rowArray, ['nis', 'nomor_induk_siswa', 'no_induk']),
            'nama_lengkap' => $this->findColumnValue($rowArray, ['nama_lengkap', 'nama lengkap', 'nama', 'name']),
            'nama_kelas' => $this->findColumnValue($rowArray, ['kelas', 'nama_kelas', 'nama kelas', 'class']),
            'jenis_kelamin' => $this->findColumnValue($rowArray, ['jenis_kelamin', 'jenis kelamin', 'kelamin', 'gender', 'jk']),
            'tanggal_lahir' => $this->findColumnValue($rowArray, ['tanggal_lahir', 'tanggal lahir', 'tgl_lahir', 'birth_date']),
            'alamat' => $this->findColumnValue($rowArray, ['alamat', 'address', 'addr']),
        ];

        return [
            'nis' => trim($mapping['nis'] ?? ''),
            'nama_lengkap' => trim($mapping['nama_lengkap'] ?? ''),
            'nama_kelas' => trim($mapping['nama_kelas'] ?? ''),
            'jenis_kelamin' => strtoupper(trim($mapping['jenis_kelamin'] ?? '')),
            'tanggal_lahir' => $this->parseTanggalLahir($mapping['tanggal_lahir'] ?? ''),
            'alamat' => trim($mapping['alamat'] ?? ''),
        ];
    }

    /**
     * Cari nilai kolom berdasarkan berbagai kemungkinan nama kolom
     */
    private function findColumnValue($rowArray, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            // Cek dengan key asli
            if (isset($rowArray[$key])) {
                return $rowArray[$key];
            }

            // Cek dengan lowercase
            $lowerKey = strtolower($key);
            if (isset($rowArray[$lowerKey])) {
                return $rowArray[$lowerKey];
            }

            // Cek dengan underscore ke space
            $spaceKey = str_replace('_', ' ', $key);
            if (isset($rowArray[$spaceKey])) {
                return $rowArray[$spaceKey];
            }

            // Cek semua keys yang ada di array dengan perbandingan case-insensitive
            foreach ($rowArray as $actualKey => $value) {
                if (strtolower(str_replace([' ', '_'], '', $actualKey)) === strtolower(str_replace([' ', '_'], '', $key))) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Parse tanggal lahir dari berbagai format
     */
    private function parseTanggalLahir($tanggal)
    {
        if (empty($tanggal)) {
            return null;
        }

        try {
            // Jika berupa Excel date number
            if (is_numeric($tanggal)) {
                return Carbon::createFromFormat('Y-m-d', gmdate('Y-m-d', ($tanggal - 25569) * 86400));
            }

            // Bersihkan string tanggal
            $tanggal = trim($tanggal);

            // Parse berbagai format tanggal
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y', 'Y/m/d', 'd.m.Y', 'Y.m.d', 'd M Y', 'M d, Y', 'Y M d', 'd F Y', 'F d, Y', 'Y F d'];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $tanggal);
                    if ($date && $date->year >= 1900 && $date->year <= date('Y')) {
                        return $date;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Coba parse dengan Carbon
            $date = Carbon::parse($tanggal);
            if ($date && $date->year >= 1900 && $date->year <= date('Y')) {
                return $date;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$tanggal}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validasi data row
     */
    private function validateRowData($data, $rowNumber)
    {
        $rules = [
            'nis' => 'required|string|max:20',
            'nama_lengkap' => 'required|string|max:255',
            'nama_kelas' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string|max:500',
        ];

        $messages = [
            'nis.required' => 'NIS harus diisi',
            'nis.max' => 'NIS maksimal 20 karakter',
            'nama_lengkap.required' => 'Nama lengkap harus diisi',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter',
            'nama_kelas.required' => 'Kelas harus diisi',
            'jenis_kelamin.required' => 'Jenis kelamin harus diisi',
            'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan)',
            'tanggal_lahir.required' => 'Tanggal lahir harus diisi',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            'alamat.max' => 'Alamat maksimal 500 karakter',
        ];

        return Validator::make($data, $rules, $messages);
    }

    /**
     * Cek akses kelas berdasarkan role user
     */
    private function canAccessKelas($kelasId)
    {
        $user = auth()->user();

        // Admin dan Kepala Sekolah bisa akses semua kelas
        if ($user->hasRole(['super_admin','Admin', 'Kepala Sekolah'])) {
            return true;
        }

        // Wali Kelas hanya bisa akses kelas yang dia pegang
        if ($user->hasRole('Wali Kelas')) {
            $waliKelas = $user->waliKelas;
            return $waliKelas && $waliKelas->kelas_id == $kelasId;
        }

        return false;
    }

    /**
     * Batch size untuk insert
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size untuk membaca file
     */
    public function chunkSize(): int
    {
        return 100;
    }

    /**
     * Getter methods
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getPreviewData(): array
    {
        return $this->previewData;
    }
}
