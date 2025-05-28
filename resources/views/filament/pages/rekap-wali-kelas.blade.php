<x-filament-panels::page>
    {{-- <div class="mt-6">
        <x-filament::button color="primary">
            <a href="{{ route('filament.admin.pages.presensi-kelas') }}" class="quick-action">
                <x-heroicon-o-clipboard-document-check class="w-8 h-8 mx-auto mb-2 text-blue-600" />
                <div class="font-semibold">Input Presensi Hari Ini</div>
                <div class="text-sm text-gray-600">Catat kehadiran siswa</div>
            </a>
        </x-filament::button>
    </div> --}}
    {{-- Header dengan informasi periode --}}
    <div class="mb-6 p-4 bg-white rounded-lg shadow-sm dark:bg-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Filter Rekap Presensi
        </h3>
        {{ $this->form }}
    </div>

    {{-- Statistik Card --}}
    @if($this->tanggal_mulai && $this->tanggal_selesai)
        @php
            $stats = $this->getRingkasanStats();
            $totalPresensi = $stats['total_kehadiran'] + $stats['total_izin'] + $stats['total_sakit'] + $stats['total_alpha'];

            // Menghitung persentase untuk setiap status
            $hadir_persen = $totalPresensi > 0 ? round(($stats['total_kehadiran'] / $totalPresensi) * 100, 2) : 0;
            $izin_persen = $totalPresensi > 0 ? round(($stats['total_izin'] / $totalPresensi) * 100, 2) : 0;
            $sakit_persen = $totalPresensi > 0 ? round(($stats['total_sakit'] / $totalPresensi) * 100, 2) : 0;
            $alpha_persen = $totalPresensi > 0 ? round(($stats['total_alpha'] / $totalPresensi) * 100, 2) : 0;

            // Menyiapkan data untuk komponen
            $total_kehadiran = [
                'hadir' => $stats['total_kehadiran'],
                'hadir_persen' => $hadir_persen,
                'izin' => $stats['total_izin'],
                'izin_persen' => $izin_persen,
                'sakit' => $stats['total_sakit'],
                'sakit_persen' => $sakit_persen,
                'alpha' => $stats['total_alpha'],
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
                        <div class="rounded-full bg-danger-500/10 p-3 text-danger-500 dark:bg-danger-500/20">
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

    {{-- Tabel Data --}}
    <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
        {{ $this->table }}
    </div>

    {{-- Info Footer --}}
    <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
        <p>Data diperbarui secara real-time • Terakhir dimuat: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</x-filament-panels::page>
