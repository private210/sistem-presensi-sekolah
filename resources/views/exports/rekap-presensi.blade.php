<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Presensi</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { margin-bottom: 20px; }
        .stats { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REKAP PRESENSI SISWA</h2>
        <p><strong>Periode:</strong> {{ $tanggal_mulai ? \Carbon\Carbon::parse($tanggal_mulai)->format('d/m/Y') : '' }} - {{ $tanggal_selesai ? \Carbon\Carbon::parse($tanggal_selesai)->format('d/m/Y') : '' }}</p>
        @if($kelas)
            <p><strong>Kelas:</strong> {{ $kelas->nama_kelas }}</p>
        @endif
        <p><strong>Diekspor pada:</strong> {{ $exported_at }}</p>
    </div>

    @if(isset($stats))
    <div class="stats">
        <h3>Ringkasan Statistik</h3>
        <table style="width: 50%; margin-bottom: 20px;">
            <tr>
                <td><strong>Total Siswa</strong></td>
                <td>{{ $stats['total_siswa'] }}</td>
            </tr>
            <tr>
                <td><strong>Total Presensi</strong></td>
                <td>{{ $stats['total_presensi'] }}</td>
            </tr>
            <tr>
                <td><strong>Hadir</strong></td>
                <td>{{ $stats['total_kehadiran'] }}</td>
            </tr>
            <tr>
                <td><strong>Izin</strong></td>
                <td>{{ $stats['total_izin'] }}</td>
            </tr>
            <tr>
                <td><strong>Sakit</strong></td>
                <td>{{ $stats['total_sakit'] }}</td>
            </tr>
            <tr>
                <td><strong>Tanpa Keterangan</strong></td>
                <td>{{ $stats['total_alpha'] }}</td>
            </tr>
            <tr>
                <td><strong>Persentase Kehadiran</strong></td>
                <td>{{ $stats['total_presensi'] > 0 ? round(($stats['total_kehadiran'] / $stats['total_presensi']) * 100, 2) : 0 }}%</td>
            </tr>
        </table>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kelas</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Status</th>
                <th>Pertemuan Ke</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_presensi)->format('d/m/Y') }}</td>
                    <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->siswa->nis ?? '-' }}</td>
                    <td>{{ $item->siswa->nama_lengkap ?? '-' }}</td>
                    <td>{{ $item->status }}</td>
                    <td>{{ $item->pertemuan_ke }}</td>
                    <td>{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data presensi</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
