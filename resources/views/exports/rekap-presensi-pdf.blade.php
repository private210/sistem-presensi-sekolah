<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Presensi Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 15px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }

        .header h2 {
            color: #1e40af;
            margin: 0 0 5px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header h3 {
            color: #64748b;
            margin: 0;
            font-size: 14px;
            font-weight: normal;
        }

        .info-section {
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 8px;
            border: none;
            font-size: 10px;
        }

        .info-table td.label {
            font-weight: bold;
            width: 15%;
            color: #475569;
        }

        .info-table td.separator {
            width: 2%;
            text-align: center;
        }

        .info-table td.value {
            width: 33%;
        }

        .stats-section {
            margin-bottom: 20px;
        }

        .stats-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e2e8f0;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border: 1px solid #d1d5db;
        }

        .stats-table td {
            border: 1px solid #d1d5db;
            padding: 6px 10px;
            font-size: 10px;
        }

        .stats-table td.stat-label {
            background-color: #f1f5f9;
            font-weight: bold;
            width: 20%;
            color: #374151;
        }

        .data-table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
            font-size: 9px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            text-align: center;
        }

        .data-table th {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }

        .data-table td.nama-siswa {
            text-align: left;
        }

        .data-table td.keterangan {
            text-align: left;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .data-table tbody tr:hover {
            background-color: #e2e8f0;
        }

        .text-center {
            text-align: center;
        }

        .no-data {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 20px;
        }

        .footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #6b7280;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            body {
                margin: 0;
                padding: 15px;
            }

            .no-print {
                display: none;
            }
        }

        .sub-header {
            background-color: #1e40af !important;
            color: white !important;
            font-weight: bold;
            text-align: center;
        }

        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .absence-formula {
            margin-bottom: 30px;
        }

        .absence-formula .formula-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .absence-formula .formula-content {
            margin-left: 20px;
        }

        .underline {
            text-decoration: underline;
        }

        .signatures {
            width: 100%;
            margin-top: 30px;
        }

        .signature-col {
            width: 33%;
            vertical-align: top;
            text-align: center;
        }

        .signature-space {
            height: 70px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .multiple-signatures {
            margin-top: 30px;
            border: 1px solid #e2e8f0;
            padding: 15px;
            background-color: #f8fafc;
        }

        .multiple-signatures-title {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }

        .wali-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .wali-item {
            width: 30%;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP PRESENSI SISWA</h2>
        <h3>{{ config('app.name', 'Sistem Presensi Sekolah') }}</h3>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Periode</td>
                <td class="separator">:</td>
                <td class="value">
                    {{ $tanggal_mulai ? \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') : '-' }}
                    s/d
                    {{ $tanggal_selesai ? \Carbon\Carbon::parse($tanggal_selesai)->format('d/m/Y') : '-' }}
                </td>
                <td class="label">Dicetak pada</td>
                <td class="separator">:</td>
                <td class="value">{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</td>
            </tr>
            @if($kelas)
            <tr>
                <td class="label">Kelas</td>
                <td class="separator">:</td>
                <td class="value">{{ $kelas->nama_kelas }}</td>
                <td class="label">Total Siswa</td>
                <td class="separator">:</td>
                <td class="value">{{ isset($groupedData) ? $groupedData->count() : $data->count() }} siswa</td>
            </tr>
            @endif
            <tr>
                <td class="label">Total Data</td>
                <td class="separator">:</td>
                <td class="value">{{ $data->count() }} record</td>
                <td class="label">Dicetak oleh</td>
                <td class="separator">:</td>
                <td class="value">{{ auth()->user()->name }}</td>
            </tr>
        </table>
    </div>

    <div class="stats-section">
        <div class="stats-title">📊 Ringkasan Statistik</div>
        <table class="stats-table">
            <tr>
                <td class="stat-label">Total Kehadiran</td>
                <td class="text-center">{{ $data->where('status', 'Hadir')->count() }}</td>
                <td class="stat-label">Persentase</td>
                <td class="text-center">{{ $data->count() > 0 ? round(($data->where('status', 'Hadir')->count() / $data->count()) * 100, 1) : 0 }}%</td>

                <td class="stat-label">Total Sakit</td>
                <td class="text-center">{{ $data->where('status', 'Sakit')->count() }}</td>
                <td class="stat-label">Persentase</td>
                <td class="text-center">{{ $data->count() > 0 ? round(($data->where('status', 'Sakit')->count() / $data->count()) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td class="stat-label">Total Izin</td>
                <td class="text-center">{{ $data->where('status', 'Izin')->count() }}</td>
                <td class="stat-label">Persentase</td>
                <td class="text-center">{{ $data->count() > 0 ? round(($data->where('status', 'Izin')->count() / $data->count()) * 100, 1) : 0 }}%</td>

                <td class="stat-label">Tanpa Keterangan</td>
                <td class="text-center">{{ $data->where('status', 'Alpa')->count() }}</td>
                <td class="stat-label">Persentase</td>
                <td class="text-center">{{ $data->count() > 0 ? round(($data->where('status', 'Alpa')->count() / $data->count()) * 100, 1) : 0 }}%</td>
            </tr>
        </table>
    </div>

    <div class="stats-title">📋 Data Presensi Detail</div>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">No</th>
                <th rowspan="2" style="width: 10%;">Kelas</th>
                <th rowspan="2" style="width: 12%;">NIS</th>
                <th rowspan="2" style="width: 25%;">Nama Siswa</th>
                <th rowspan="2" style="width: 12%;">Jumlah hari/bulan</th>
                <th rowspan="2" style="width: 12%;">Jumlah Hadir</th>
                <th colspan="3" style="width: 15%;">Jumlah Ke-tidak Hadiran</th>
                <th rowspan="2" style="width: 10%;">Jumlah Total</th>
                <th rowspan="2" style="width: 10%;">Keterangan</th>
            </tr>
            <tr class="sub-header">
                <th style="width: 5%;">S</th>
                <th style="width: 5%;">I</th>
                <th style="width: 5%;">A</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Properly process the data to extract student information
                $groupedData = $data->groupBy('siswa_id')->map(function ($studentData) {
                    $presensi = $studentData->first(); // Get first presensi record
                    $jumlah_hari = $studentData->count();
                    $jumlah_hadir = $studentData->where('status', 'Hadir')->count();
                    $jumlah_sakit = $studentData->where('status', 'Sakit')->count();
                    $jumlah_izin = $studentData->where('status', 'Izin')->count();
                    $jumlah_alpha = $studentData->where('status', 'Alpa')->count();

                    $percentage = $jumlah_hari > 0 ? ($jumlah_hadir / $jumlah_hari) * 100 : 0;
                    $keterangan = 'Sangat Kurang';
                    if ($percentage >= 90) $keterangan = 'Baik';
                    elseif ($percentage >= 80) $keterangan = 'Cukup';
                    elseif ($percentage >= 70) $keterangan = 'Kurang';

                    return [
                        'presensi' => $presensi,
                        'siswa' => $presensi->siswa, // Access through relationship
                        'kelas' => $presensi->kelas, // Access through relationship
                        'jumlah_hari' => $jumlah_hari,
                        'jumlah_hadir' => $jumlah_hadir,
                        'jumlah_sakit' => $jumlah_sakit,
                        'jumlah_izin' => $jumlah_izin,
                        'jumlah_alpha' => $jumlah_alpha,
                        'jumlah_total' => $jumlah_sakit + $jumlah_izin + $jumlah_alpha,
                        'keterangan' => $keterangan
                    ];
                })->values();
            @endphp

            @forelse($groupedData as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['kelas']->nama_kelas ?? '-' }}</td>
                    <td>{{ $item['siswa']->nis ?? '-' }}</td>
                    <td class="nama-siswa">{{ $item['siswa']->nama_lengkap ?? '-' }}</td>
                    <td>{{ $item['jumlah_hari'] }}</td>
                    <td>{{ $item['jumlah_hadir'] }}</td>
                    <td>{{ $item['jumlah_sakit'] }}</td>
                    <td>{{ $item['jumlah_izin'] }}</td>
                    <td>{{ $item['jumlah_alpha'] }}</td>
                    <td>{{ $item['jumlah_total'] }}</td>
                    <td class="keterangan">{{ $item['keterangan'] }}</td>
                </tr>

                {{-- Page break setiap 25 baris untuk mencegah tabel terpotong --}}
                @if(($index + 1) % 25 == 0 && !$loop->last)
                    </tbody>
                    </table>
                    <div class="page-break"></div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 4%;">No</th>
                                <th rowspan="2" style="width: 10%;">Kelas</th>
                                <th rowspan="2" style="width: 12%;">NIS</th>
                                <th rowspan="2" style="width: 25%;">Nama Siswa</th>
                                <th rowspan="2" style="width: 12%;">Jumlah hari/bulan</th>
                                <th rowspan="2" style="width: 12%;">Jumlah Hadir</th>
                                <th colspan="3" style="width: 15%;">Jumlah Ke-tidak Hadiran</th>
                                <th rowspan="2" style="width: 10%;">Jumlah Total</th>
                                <th rowspan="2" style="width: 10%;">Keterangan</th>
                            </tr>
                            <tr class="sub-header">
                                <th style="width: 5%;">S</th>
                                <th style="width: 5%;">I</th>
                                <th style="width: 5%;">A</th>
                            </tr>
                        </thead>
                        <tbody>
                @endif
            @empty
                <tr>
                    <td colspan="11" class="no-data">
                        📭 Tidak ada data presensi untuk periode ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Absence rate calculation and signature section -->
    <div class="signature-section">
        <div class="absence-formula">
            <div class="formula-title">Keterangan :</div>
            <div class="formula-content">
                % Absen rata-rata bulan ini =
                <span class="underline">Jumlah siswa dalam sebulan</span> x 100%<br>
                <span style="margin-left: 180px;">Jumlah siswa x hari masuk</span>

                @php
                    // Calculate total student count
                    $totalSiswa = $groupedData->count();

                    // Calculate total school days in the date range
                    $startDate = \Carbon\Carbon::parse($tanggal_mulai);
                    $endDate = \Carbon\Carbon::parse($tanggal_selesai);
                    $totalDays = $startDate->diffInDaysFiltered(function (\Carbon\Carbon $date) {
                        return $date->isWeekday(); // Only count weekdays (Monday to Friday)
                    }, $endDate) + 1; // +1 to include the end date

                    // Calculate total absences
                    $totalAbsences = $data->whereIn('status', ['Sakit', 'Izin', 'Alpa'])->count();

                    // Calculate maximum possible attendances
                    $maxAttendances = $totalSiswa * $totalDays;

                    // Calculate absence percentage
                    $absentPercentage = ($maxAttendances > 0) ? ($totalAbsences / $maxAttendances) * 100 : 0;
                @endphp

                <div style="margin-top: 10px; margin-left: 180px;">
                    = <span style="margin-left: 10px;">{{ $totalAbsences }}</span> x 100% = {{ number_format($absentPercentage, 1) }}%<br>
                    <span style="margin-left: 10px;">{{ $totalSiswa }} x {{ $totalDays }}</span>
                </div>
            </div>
        </div>

        @if($kelas && $wali_kelas)
            {{-- Jika export untuk kelas tertentu dan ada wali kelas --}}
            <table class="signatures">
                <tr>
                    <td class="signature-col">
                        <div>Mengetahui,</div>
                        <div>Kepala Sekolah</div>
                        <div class="signature-space"></div>
                        <div class="signature-name">
                            @if($kepala_sekolah)
                                {{ $kepala_sekolah->nama_lengkap }}
                            @else
                                {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                            @endif
                        </div>
                        <div>
                            NIP.
                            @if($kepala_sekolah)
                                {{ $kepala_sekolah->nip ?? 'N/A' }}
                            @else
                                {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                            @endif
                        </div>
                    </td>
                    <td class="signature-col">
                        <!-- Empty middle column for spacing -->
                    </td>
                    <td class="signature-col">
                        {{ config('app.school_city', 'Banjarejo') }}, {{ \Carbon\Carbon::now()->format('d-m-Y') }}<br>
                        Wali  {{ $kelas->nama_kelas }}
                        <div class="signature-space"></div>
                        <div class="signature-name">
                            {{ $wali_kelas->nama_lengkap }}
                        </div>
                        <div>
                            NIP. {{ $wali_kelas->nip ?? 'N/A' }}
                        </div>
                    </td>
                </tr>
            </table>
        @elseif(!$kelas && isset($all_wali_kelas) && $all_wali_kelas->count() > 0)
            {{-- Jika export semua kelas dan ada multiple wali kelas --}}
            <table class="signatures">
                <tr>
                    <td colspan="3" style="text-align: center;">
                        <div>Mengetahui,</div>
                        <div>Kepala Sekolah</div>
                        <div class="signature-space"></div>
                        <div class="signature-name">
                            @if($kepala_sekolah)
                                {{ $kepala_sekolah->nama_lengkap }}
                            @else
                                {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                            @endif
                        </div>
                        <div>
                            NIP.
                            @if($kepala_sekolah)
                                {{ $kepala_sekolah->nip ?? 'N/A' }}
                            @else
                                {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                            @endif
                        </div>
                    </td>
                </tr>
            </table>

            <div class="multiple-signatures">
                <div class="multiple-signatures-title">Wali Kelas yang Terlibat:</div>
                <div class="wali-list">
                    @foreach($all_wali_kelas as $wk)
                        <div class="wali-item">
                            <strong>{{ $wk->kelas->nama_kelas }}</strong><br>
                            {{ $wk->nama_lengkap }}<br>
                            NIP. {{ $wk->nip ?? 'N/A' }}
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Fallback jika tidak ada wali kelas --}}
            <table class="signatures">
                <tr>
                    <td class="signature-col">
                        <div>Mengetahui,</div>
                        <div>Kepala Sekolah</div>
                        <div class="signature-space"></div>
                        <div class="signature-name">
                            @if($kepala_sekolah)
                                {{ $kepala_sekolah->nama_lengkap }}
                            @else
                                {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                            @endif
                        </div>
                        <div>
                            NIP.
                            @if($kepala_sekolah)
                                {{ $kepala_sekolah->nip ?? 'N/A' }}
                            @else
                                {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                            @endif
                        </div>
                    </td>
                    <td class="signature-col">
                        <!-- Empty middle column for spacing -->
                    </td>
                    <td class="signature-col">
                        {{ config('app.school_city', 'Banjarejo') }}, {{ \Carbon\Carbon::now()->format('d-m-Y') }}<br>
                        Wali Kelas
                        <div class="signature-space"></div>
                        <div class="signature-name">
                            ________________________
                        </div>
                        <div>
                            NIP. ________________________
                        </div>
                    </td>
                </tr>
            </table>
        @endif
    </div>
    <div class="footer clearfix">
        <div class="footer-left">
            <strong>{{ config('app.name', 'Sistem Presensi') }}</strong><br>
            Dicetak oleh: {{ auth()->user()->name }}<br>
            Tanggal: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
        </div>
        <div class="footer-right">
            <strong>Keterangan:</strong><br>
            S = Sakit | I = Izin | A = Alpha (Tanpa Keterangan)
        </div>
    </div>
</body>
</html>
