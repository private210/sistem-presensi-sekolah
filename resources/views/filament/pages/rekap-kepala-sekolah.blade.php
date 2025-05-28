<x-filament-panels::page>

    @if($this->tanggal_mulai && $this->tanggal_selesai)
    @php
    $stats = $this->getKelasStats();
        $totalHadir = $stats->sum('total_hadir');
        $totalIzin = $stats->sum('total_izin');
        $totalSakit = $stats->sum('total_sakit');
        $totalAlpha = $stats->sum('total_alpha');
        $totalPresensi = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;

        // Menghitung persentase untuk setiap status
        $hadir_persen = $totalPresensi > 0 ? round(($totalHadir / $totalPresensi) * 100, 2) : 0;
        $izin_persen = $totalPresensi > 0 ? round(($totalIzin / $totalPresensi) * 100, 2) : 0;
        $sakit_persen = $totalPresensi > 0 ? round(($totalSakit/ $totalPresensi) * 100, 2) : 0;
        $alpha_persen = $totalPresensi > 0 ? round(($totalAlpha / $totalPresensi) * 100, 2) : 0;

        // Menyiapkan data untuk komponen
        $total_kehadiran = [
            'hadir' => $totalHadir,
            'hadir_persen' => $hadir_persen,
            'izin' => $totalIzin,
            'izin_persen' => $izin_persen,
            'sakit' => $totalSakit,
            'sakit_persen' => $sakit_persen,
            'alpha' => $totalAlpha,
            'alpha_persen' => $alpha_persen,
        ];
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            Ringkasan Kehadiran
        </x-slot>

        <x-slot name="description">
            Periode: {{ \Carbon\Carbon::parse($this->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($this->tanggal_selesai)->format('d/m/Y') }}
        </x-slot>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            {{-- Card Hadir --}}
            <div class="rounded-xl bg-success-50 p-6 dark:bg-success-950">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-success-600 dark:text-success-400">Hadir</p>
                        <p class="text-3xl font-semibold text-success-950 dark:text-white">
                            {{ $total_kehadiran['hadir'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-full bg-success-500/10 p-3 text-success-500 dark:bg-success-500/20">
                        <x-filament::icon icon="heroicon-o-check-circle"  class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4">
                    <div class="h-2 w-full rounded-full bg-success-200 dark:bg-success-700">
                        <div class="h-2 rounded-full bg-success-500 transition-all duration-300"
                             style="width: {{ min(($total_kehadiran['hadir_persen'] ?? 0), 100) }}%"></div>
                    </div>
                    <p class="mt-1 text-xs text-success-500">{{ $total_kehadiran['hadir_persen'] ?? 0 }}% dari total</p>
                </div>
            </div>

            {{-- Card Izin --}}
            <div class="rounded-xl bg-info-50 p-6 dark:bg-info-950">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-info-600 dark:text-info-400">Izin</p>
                        <p class="text-3xl font-semibold text-info-950 dark:text-white">
                            {{ $total_kehadiran['izin'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-full bg-info-500/10 p-3 text-info-500 dark:bg-info-500/20">
                        <x-filament::icon icon="heroicon-o-document-text" class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4">
                    <div class="h-2 w-full rounded-full bg-info-200 dark:bg-info-700">
                        <div class="h-2 rounded-full bg-info-500 transition-all duration-300"
                             style="width: {{ min(($total_kehadiran['izin_persen'] ?? 0), 100) }}%"></div>
                    </div>
                    <p class="mt-1 text-xs text-info-500">{{ $total_kehadiran['izin_persen'] ?? 0 }}% dari total</p>
                </div>
            </div>

            {{-- Card Sakit --}}
            <div class="rounded-xl bg-warning-50 p-6 dark:bg-warning-950">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-warning-600 dark:text-warning-400">Sakit</p>
                        <p class="text-3xl font-semibold text-warning-950 dark:text-white">
                            {{ $total_kehadiran['sakit'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-full bg-warning-500/10 p-3 text-warning-500 dark:bg-warning-500/20">
                        <x-filament::icon icon="heroicon-o-heart" class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4">
                    <div class="h-2 w-full rounded-full bg-warning-200 dark:bg-warning-700">
                        <div class="h-2 rounded-full bg-warning-500 transition-all duration-300"
                             style="width: {{ min(($total_kehadiran['sakit_persen'] ?? 0), 100) }}%"></div>
                    </div>
                    <p class="mt-1 text-xs text-warning-500">{{ $total_kehadiran['sakit_persen'] ?? 0 }}% dari total</p>
                </div>
            </div>

            {{-- Card Tanpa Keterangan --}}
            <div class="rounded-xl bg-danger-50 p-6 dark:bg-danger-950">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Tanpa Keterangan</p>
                        <p class="text-3xl font-semibold text-danger-950 dark:text-white">
                            {{ $total_kehadiran['alpha'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-full bg-danger-500/10 p-3 dark:bg-danger-500/20">
                        <x-filament::icon icon="heroicon-o-x-circle" class="h-6 w-6" />
                    </div>
                </div>
                <div class="mt-4">
                    <div class="h-2 w-full rounded-full bg-danger-200 dark:bg-danger-700">
                        <div class="h-2 rounded-full bg-danger-500 transition-all duration-300"
                             style="width: {{ min(($total_kehadiran['alpha_persen'] ?? 0), 100) }}%"></div>
                    </div>
                    <p class="mt-1 text-xs text-danger-500">{{ $total_kehadiran['alpha_persen'] ?? 0 }}% dari total</p>
                </div>
            </div>
        </div>
    </x-filament::section>
@endif
    {{-- Statistik Cards --}}
    @php
        $stats = $this->getKelasStats();
        $totalSiswa = $stats->sum('total_siswa');
        $totalHadir = $stats->sum('total_hadir');
        $totalIzin = $stats->sum('total_izin');
        $totalSakit = $stats->sum('total_sakit');
        $totalAlpha = $stats->sum('total_alpha');
        $totalPresensi = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;
        $persentaseKehadiran = ($totalSiswa > 0 && $totalPresensi > 0)
    ? round(($totalHadir / $totalPresensi) * 100, 2)
    : 0;
    @endphp

    {{-- Statistik Per Kelas --}}
    @if($stats->isNotEmpty())
    <div class="mb-6">
        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistik Per Kelas</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($stats as $kelas)
                @php
                    $totalPresensi = $kelas->total_hadir + $kelas->total_izin + $kelas->total_sakit + $kelas->total_alpha;
                    $persentaseKelas = $totalPresensi > 0 ? round(($kelas->total_hadir / $totalPresensi) * 100, 1) : 0;
                @endphp
                <div class="bg-white p-4 rounded-lg shadow-sm border dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="font-semibold text-gray-900 dark:text-white">{{ $kelas->nama_kelas }}</h5>
                        <span class="text-sm font-medium px-2 py-1 rounded-full {{ $persentaseKelas >= 80 ? 'bg-green-100 text-green-800' : ($persentaseKelas >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $persentaseKelas }}%
                        </span>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Siswa:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $kelas->total_siswa }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-green-600">Hadir:</span>
                            <span class="font-medium text-green-600">{{ $kelas->total_hadir }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-600">Izin:</span>
                            <span class="font-medium text-blue-600">{{ $kelas->total_izin }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-yellow-600">Sakit:</span>
                            <span class="font-medium text-yellow-600">{{ $kelas->total_sakit }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-red-600">Alpha:</span>
                            <span class="font-medium text-red-600">{{ $kelas->total_alpha }}</span>
                        </div>
                    </div>

                    {{-- Mini Progress Bar --}}
                    <div class="mt-3">
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $persentaseKelas }}%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tabel Data Presensi --}}
    <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Data Presensi Detail</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">Data presensi siswa dikelompokkan berdasarkan kelas</p>
        </div>
        <div class="p-4">
            {{ $this->table }}
        </div>
    </div>

    {{-- Info Footer --}}
    <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
        <p>Data diperbarui secara real-time • Terakhir dimuat: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</x-filament-panels::page>
