<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Presensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info table td {
            padding: 5px;
            border: none;
        }
        .stats {
            margin-bottom: 20px;
        }
        .stats table {
            width: 50%;
            border-collapse: collapse;
        }
        .stats table td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        .data-table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        .status-hadir { color: green; font-weight: bold; }
        .status-izin { color: blue; font-weight: bold; }
        .status-sakit { color: orange; font-weight: bold; }
        .status-alpha { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP PRESENSI SISWA</h2>
        <h3>{{ config('app.name', 'Sistem Presensi Sekolah') }}</h3>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="15%"><strong>Periode</strong></td>
                <td width="35%">: {{ $tanggal_mulai ? \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') : '' }} - {{ $tanggal_selesai ? \Carbon\Carbon::parse($tanggal_selesai)->format('d/m/Y') : '' }}</td>
                <td width="15%"><strong>Dicetak pada</strong></td>
                <td width="35%">: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</td>
            </tr>
            @if($kelas)
            <tr>
                <td><strong>Kelas</strong></td>
                <td>: {{ $kelas->nama_kelas }}</td>
                <td><strong>Total Siswa</strong></td>
                <td>: {{ $data->unique('siswa_id')->count() }} siswa</td>
            </tr>
            @endif
            <tr>
                <td><strong>Total Data</strong></td>
                <td>: {{ $data->count() }} record</td>
                <td><strong>Dicetak oleh</strong></td>
                <td>: {{ auth()->user()->name }}</td>
            </tr>
        </table>
    </div>

    <div class="stats">
        <h4>Ringkasan Statistik</h4>
        <table>
            <tr>
                <td><strong>Total Kehadiran</strong></td>
                <td class="text-center">{{ $data->where('status', 'Hadir')->count() }}</td>
                <td><strong>Persentase</strong></td>
                <td class="text-center">{{ $data->count() > 0 ? round(($data->where('status', 'Hadir')->count() / $data->count()) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td><strong>Total Izin</strong></td>
                <td class="text-center">{{ $data->where('status', 'Izin')->count() }}</td>
                <td><strong>Persentase</strong></td>
                <td class="text-center">{{ $data->count() > 0 ? round(($data->where('status', 'Izin')->count() / $data->count()) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td><strong>Total Sakit</strong></td>
                <td class="text-center">{{ $data->where('status', 'Sakit')->count() }}</td>
                <td><strong>Persentase</strong></td>
                <td class="text-center">{{ $data->count() > 0 ? round(($data->where('status', 'Sakit')->count() / $data->count()) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td><strong>Tanpa Keterangan</strong></td>
                <td class="text-center">{{ $data->where('status', 'Tanpa Keterangan')->count() }}</td>
                <td><strong>Persentase</strong></td>
                <td class="text-center">{{ $data->count() > 0 ? round(($data->where('status', 'Tanpa Keterangan')->count() / $data->count()) * 100, 1) : 0 }}%</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="10%">Kelas</th>
                <th width="12%">NIS</th>
                <th width="25%">Nama Siswa</th>
                <th width="12%">Status</th>
                <th width="8%">Pertemuan</th>
                <th width="16%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal_presensi)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $item->kelas->nama_kelas ?? '-' }}</td>
                    <td class="text-center">{{ $item->siswa->nis ?? '-' }}</td>
                    <td>{{ $item->siswa->nama_lengkap ?? '-' }}</td>
                    <td class="text-center">
                        <span class="status-{{ strtolower(str_replace(' ', '', $item->status)) }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="text-center">{{ $item->pertemuan_ke }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data presensi untuk periode ini</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        <p>Halaman {{ $loop->iteration ?? 1 }}</p>
    </div>
</body>
</html>
