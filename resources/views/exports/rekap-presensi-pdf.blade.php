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
            margin-top: 20px; /* Reduced from 25px */
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

            .signature-section {
                page-break-inside: avoid;
            }

            .signature-wrapper {
                page-break-inside: avoid;
            }

            .signature-right-bottom {
                position: relative !important;
                float: right !important;
                margin-right: 100px !important;
            }

            .footer {
                position: relative;
                margin-top: 30px !important; /* Reduced from 100px */
                page-break-inside: avoid;
            }
        }

        .sub-header {
            background-color: #1e40af !important;
            color: white !important;
            font-weight: bold;
            text-align: center;
        }

        .signature-section {
            margin-top: 30px; /* Reduced from 50px */
            page-break-inside: avoid;
            clear: both;
            position: relative;
        }

        .absence-formula {
            margin-bottom: 40px; /* Reduced from 80px */
            clear: both;
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

        /* Updated signature styles for better positioning */
        .signatures {
            width: 100%;
            margin-top: 20px; /* Reduced from 30px */
            position: relative;
            clear: both;
        }

        .signature-right-bottom {
            position: relative;
            float: right;
            text-align: center;
            width: 300px;
            margin-right: 120px; /* Moved 120px to the left from right edge */
            clear: both;
        }

        .signature-space {
            height: 50px; /* Reduced from 60px */
            margin: 10px 0;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 3px;
        }

        .signature-title {
            font-weight: normal;
            margin-bottom: 5px;
        }

        .signature-nip {
            font-weight: normal;
        }

        .signature-col {
            width: 33%;
            vertical-align: top;
            text-align: center;
        }

        /* New style for two-column signature layout */
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 0 20px;
            vertical-align: top;
        }

        .multiple-signatures {
            margin-top: 30px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            background-color: #f8fafc;
            page-break-inside: avoid;
            clear: both;
        }

        .multiple-signatures-title {
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            font-size: 11px;
        }

        /* New styles for table-like wali kelas layout */
        .wali-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            clear: both;
        }

        .wali-table td {
            border: 1px solid #d1d5db;
            padding: 15px;
            vertical-align: top;
            text-align: center;
            width: 50%;
        }

        .wali-table .teacher-info {
            padding: 20px 15px;
            height: 120px;
        }

        .wali-table .teacher-info .kelas-header {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 0;
            padding: 0;
            background-color: transparent;
        }

        .wali-table .teacher-name {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .wali-table .teacher-nip {
            font-size: 10px;
        }

        /* For 3 or more columns */
        .wali-table.three-cols td {
            width: 33.33%;
        }

        .wali-table.four-cols td {
            width: 25%;
        }

        /* Ensure proper spacing between formula and signature */
        .signature-wrapper {
            position: relative;
            min-height: 180px; /* Reduced from 250px */
            clear: both;
            margin-top: 20px;
            display: block;
            width: 100%;
        }

        /* Add clearfix to signature section */
        .signature-section::after {
            content: "";
            display: table;
            clear: both;
        }

        @media print {
            .multiple-signatures {
                padding: 15px;
            }

            .wali-table .teacher-info {
                height: 100px;
                padding: 15px 10px;
            }
        }
    </style>
</head>
<body>
    @php
        // Check if this is a wali murid export
        $isWaliMurid = isset($is_wali_murid) && $is_wali_murid === true;
    @endphp

    <!-- HEADER SECTION -->
    <div class="header">
        <h2>REKAP PRESENSI SISWA</h2>
        <h3>{{ config('app.name', 'Sistem Presensi Sekolah') }}</h3>
    </div>

    <!-- INFORMATION SECTION -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Periode</td>
                <td class="separator">:</td>
                <td class="value">
                    {{ $tanggal_mulai ? \Carbon\Carbon::parse($tanggal_mulai)->translatedFormat('l, d F Y') : '-' }}
                    s/d
                    {{ $tanggal_selesai ? \Carbon\Carbon::parse($tanggal_selesai)->translatedFormat('l, d F Y') : '-' }}
                    @if(isset($periode_type) && $periode_type === 'semester')
                        @php
                            $startMonth = \Carbon\Carbon::parse($tanggal_mulai)->month;
                            $semesterType = $startMonth >= 7 ? 'Semester Ganjil' : 'Semester Genap';
                        @endphp
                        ({{ $semesterType }})
                    @endif
                </td>
                <td class="label">Dicetak pada</td>
                <td class="separator">:</td>
                <td class="value">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y').'  Pukul  '.\Carbon\Carbon::now()->format('H:i:s') }}</td>
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

    <!-- STATISTICS SECTION -->
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

    <!-- DETAIL DATA SECTION -->
    <div class="stats-title">📋 Data Presensi Detail</div>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 4%;">No</th>
                <th rowspan="2" style="width: 10%;">Kelas</th>
                <th rowspan="2" style="width: 12%;">NIS</th>
                <th rowspan="2" style="width: 25%;">Nama Siswa</th>
                <th rowspan="2" style="width: 12%;">Jumlah hari/semester</th>
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
            @if(!isset($groupedData))
            @php
                // Process data if not already processed
                $groupedData = $data->groupBy('siswa_id')->map(function ($studentData) use ($tanggal_mulai, $tanggal_selesai) {
                    $presensi = $studentData->first();

                    // Calculate total school days (weekdays only)
                    $startDate = \Carbon\Carbon::parse($tanggal_mulai);
                    $endDate = \Carbon\Carbon::parse($tanggal_selesai);
                    $totalSchoolDays = 0;
                    $current = $startDate->copy();

                    while ($current->lte($endDate)) {
                        // Skip weekends
                        if ($current->isWeekend()) {
                            $current->addDay();
                            continue;
                        }

                        // Check if it's a holiday
                        $isHoliday = \App\Models\HariLibur::where('tanggal_mulai', '<=', $current->format('Y-m-d'))
                            ->where(function ($query) use ($current) {
                                $query->whereNull('tanggal_selesai')
                                    ->where('tanggal_mulai', '=', $current->format('Y-m-d'))
                                    ->orWhere('tanggal_selesai', '>=', $current->format('Y-m-d'));
                            })
                            ->exists();

                        // Only count if it's not a holiday
                        if (!$isHoliday) {
                            $totalSchoolDays++;
                        }

                        $current->addDay();
                    }

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
            @endphp
            @endif

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
                                <th rowspan="2" style="width: 12%;">Jumlah hari/semester</th>
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

    @if(!$isWaliMurid)
    <!-- ABSENCE RATE CALCULATION AND SIGNATURE SECTION (ONLY FOR NON-WALI MURID) -->
    <div class="signature-section">
        <div class="absence-formula">
            <div class="formula-title">Keterangan :</div>
            <div class="formula-content">
                @php
                    // Calculate absence percentage
                    $totalSiswa = $groupedData->count();
                    $startDate = \Carbon\Carbon::parse($tanggal_mulai);
                    $endDate = \Carbon\Carbon::parse($tanggal_selesai);
                    $totalSchoolDays = 0;
                    $current = $startDate->copy();

                    while ($current->lte($endDate)) {
                        // Skip weekends
                        if ($current->isWeekend()) {
                            $current->addDay();
                            continue;
                        }

                        // Check if it's a holiday
                        $isHoliday = \App\Models\HariLibur::where('tanggal_mulai', '<=', $current->format('Y-m-d'))
                            ->where(function ($query) use ($current) {
                                $query->whereNull('tanggal_selesai')
                                    ->where('tanggal_mulai', '=', $current->format('Y-m-d'))
                                    ->orWhere('tanggal_selesai', '>=', $current->format('Y-m-d'));
                            })
                            ->exists();

                        // Only count if it's not a holiday
                        if (!$isHoliday) {
                            $totalSchoolDays++;
                        }

                        $current->addDay();
                    }

                    $totalAbsences = $data->whereIn('status', ['Sakit', 'Izin', 'Alpa'])->count();
                    $maxAttendances = $totalSiswa * $totalSchoolDays;
                    $absentPercentage = ($maxAttendances > 0) ? ($totalAbsences / $maxAttendances) * 100 : 0;

                    $periodText = isset($periode_type) && $periode_type === 'semester' ? 'semester ini' : 'bulan ini';
                @endphp

                % Absen rata-rata {{ $periodText }} =
                <span class="underline">Jumlah siswa dalam {{ $periodText }}</span> x 100%<br>
                <span style="margin-left: 180px;">Jumlah siswa x hari masuk</span>

                <div style="margin-top: 10px; margin-left: 180px;">
                    = <span class="underline" style="margin-left: 8px;"> {{ $totalAbsences }} x 100% </span>= {{ number_format($absentPercentage, 1) }}%<br>
                    <span style="margin-left: 15px;">{{ $totalSiswa }} x {{ $totalSchoolDays }}</span>
                </div>
            </div>
        </div>

        <!-- UPDATED SIGNATURE SECTION - Better positioned without overlap -->
        <div class="signature-wrapper">
            @if($kelas && $wali_kelas)
                <!-- Single class with wali kelas - Show both Kepala Sekolah and Wali Kelas -->
                <table style="width: 100%; margin-top: 20px;">
                    <tr>
                        <td style="width: 50%; text-align: center; padding: 0 20px;">
                            <div>Mengetahui,</div>
                            <div class="signature-title">Kepala Sekolah</div>
                            <div class="signature-space"></div>
                            <div class="signature-name">
                                @if(isset($kepala_sekolah) && $kepala_sekolah)
                                    @if($kepala_sekolah->user && $kepala_sekolah->user->name)
                                        {{ $kepala_sekolah->user->name }}
                                    @elseif(isset($kepala_sekolah->nama_lengkap))
                                        {{ $kepala_sekolah->nama_lengkap }}
                                    @else
                                        {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                                    @endif
                                @else
                                    {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                                @endif
                            </div>
                            <div class="signature-nip">
                                NIP.
                                @if(isset($kepala_sekolah) && $kepala_sekolah)
                                    @if($kepala_sekolah->user && isset($kepala_sekolah->user->nip))
                                        {{ $kepala_sekolah->user->nip ?? 'N/A' }}
                                    @elseif(isset($kepala_sekolah->nip))
                                        {{ $kepala_sekolah->nip ?? 'N/A' }}
                                    @else
                                        {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                                    @endif
                                @else
                                    {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                                @endif
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center; padding: 0 20px;">
                            <div>{{ config('app.school_city', 'Banjarejo') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                            <div class="signature-title">Wali {{ $kelas->nama_kelas }}</div>
                            <div class="signature-space"></div>
                            <div class="signature-name">
                                @if($wali_kelas->user && $wali_kelas->user->name)
                                    {{ $wali_kelas->user->name }}
                                @elseif(isset($wali_kelas->nama_lengkap))
                                    {{ $wali_kelas->nama_lengkap }}
                                @else
                                    NAMA WALI KELAS
                                @endif
                            </div>
                            <div class="signature-nip">
                                NIP.
                                @if($wali_kelas->user && isset($wali_kelas->user->nip))
                                    {{ $wali_kelas->user->nip ?? 'N/A' }}
                                @elseif(isset($wali_kelas->nip))
                                    {{ $wali_kelas->nip ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
            @elseif(!$kelas && isset($all_wali_kelas) && $all_wali_kelas->count() > 0)
                <!-- Multiple classes - Right positioned - ONLY KEPALA SEKOLAH -->
                <div class="signature-right-bottom">
                    <div>{{ config('app.school_city', 'Banjarejo') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                    <div class="signature-title">Kepala Satuan Pendidikan</div>
                    <div class="signature-title">SDN Banjarejo</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">
                        @if(isset($kepala_sekolah) && $kepala_sekolah)
                            @if($kepala_sekolah->user && $kepala_sekolah->user->name)
                                {{ $kepala_sekolah->user->name }}
                            @elseif(isset($kepala_sekolah->nama_lengkap))
                                {{ $kepala_sekolah->nama_lengkap }}
                            @else
                                {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                            @endif
                        @else
                            {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                        @endif
                    </div>
                    @if(isset($kepala_sekolah) && $kepala_sekolah)
                        @php
                            $pangkat = '';
                            $golongan = '';

                            if($kepala_sekolah->user && isset($kepala_sekolah->user->pangkat)) {
                                $pangkat = $kepala_sekolah->user->pangkat;
                            } elseif(isset($kepala_sekolah->pangkat)) {
                                $pangkat = $kepala_sekolah->pangkat;
                            } else {
                                $pangkat = config('app.school_principal_pangkat', '');
                            }

                            if($kepala_sekolah->user && isset($kepala_sekolah->user->golongan)) {
                                $golongan = $kepala_sekolah->user->golongan;
                            } elseif(isset($kepala_sekolah->golongan)) {
                                $golongan = $kepala_sekolah->golongan;
                            } else {
                                $golongan = config('app.school_principal_golongan', '');
                            }
                        @endphp

                        @if($pangkat && $golongan)
                            <div class="signature-title">{{ $pangkat }} ({{ $golongan }})</div>
                        @endif
                    @endif
                    <div class="signature-nip">
                        NIP.
                        @if(isset($kepala_sekolah) && $kepala_sekolah)
                            @if($kepala_sekolah->user && isset($kepala_sekolah->user->nip))
                                {{ $kepala_sekolah->user->nip ?? 'N/A' }}
                            @elseif(isset($kepala_sekolah->nip))
                                {{ $kepala_sekolah->nip ?? 'N/A' }}
                            @else
                                {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                            @endif
                        @else
                            {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                        @endif
                    </div>
                </div>
            @else
                <!-- Fallback if no wali kelas info - Show both Kepala Sekolah and Wali Kelas -->
                <table style="width: 100%; margin-top: 20px;">
                    <tr>
                        <td style="width: 50%; text-align: center; padding: 0 20px;">
                            <div>Mengetahui,</div>
                            <div class="signature-title">Kepala Sekolah</div>
                            <div class="signature-space"></div>
                            <div class="signature-name">
                                @if(isset($kepala_sekolah) && $kepala_sekolah)
                                    @if($kepala_sekolah->user && $kepala_sekolah->user->name)
                                        {{ $kepala_sekolah->user->name }}
                                    @elseif(isset($kepala_sekolah->nama_lengkap))
                                        {{ $kepala_sekolah->nama_lengkap }}
                                    @else
                                        {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                                    @endif
                                @else
                                    {{ config('app.school_principal_name', 'NAMA KEPALA SEKOLAH') }}
                                @endif
                            </div>
                            <div class="signature-nip">
                                NIP.
                                @if(isset($kepala_sekolah) && $kepala_sekolah)
                                    @if($kepala_sekolah->user && isset($kepala_sekolah->user->nip))
                                        {{ $kepala_sekolah->user->nip ?? 'N/A' }}
                                    @elseif(isset($kepala_sekolah->nip))
                                        {{ $kepala_sekolah->nip ?? 'N/A' }}
                                    @else
                                        {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                                    @endif
                                @else
                                    {{ config('app.school_principal_nip', 'NIP KEPALA SEKOLAH') }}
                                @endif
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center; padding: 0 20px;">
                            <div>{{ config('app.school_city', 'Banjarejo') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                            <div class="signature-title">Wali Kelas</div>
                            <div class="signature-space"></div>
                            <div class="signature-name">________________________</div>
                            <div class="signature-nip">NIP. ________________________</div>
                        </td>
                    </tr>
                </table>
            @endif
        </div>
    </div>
    @endif

    <!-- FOOTER -->
    <div class="footer clearfix" style="clear: both; margin-top: 30px;">
        <div class="footer-left">
            <strong>{{ config('app.name', 'Sistem Presensi') }}</strong><br>
            Dicetak oleh: {{ auth()->user()->name }}<br>
            Tanggal: {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y, H:i') }}
        </div>
        <div class="footer-right">
            <strong>Keterangan:</strong><br>
            S = Sakit | I = Izin | A = Alpha (Tanpa Keterangan)
        </div>
    </div>
</body>
</html>
