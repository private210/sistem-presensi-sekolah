<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button wire:click="generateReport" color="primary">
                <x-filament::icon icon="heroicon-m-arrow-path" class="w-4 h-4 mr-2" />
                Perbarui Laporan
            </x-filament::button>
        </div>
    </x-filament::section>

    <!-- Summary Cards -->
    <x-filament::section>
        <x-slot name="heading">
            Ringkasan Kehadiran
        </x-slot>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl bg-success-50 p-6 dark:bg-success-950">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-success-600 dark:text-success-400">Hadir</p>
                        <p class="text-3xl font-semibold text-success-950 dark:text-white">
                            {{ $total_kehadiran['hadir'] ?? 0 }}
                        </p>
                    </div>
                    <div class="rounded-full bg-success-500/10 p-3 text-success-500 dark:bg-success-500/20">
                        <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6" />
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

            <div class="rounded-xl bg-danger-50 p-6 dark:bg-danger-950">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-danger-600 dark:text-danger-400">Tanpa Keterangan</p>
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

    <div class="grid grid-cols-1 gap-8 mt-8 lg:grid-cols-2">
        <!-- Statistik Per Kelas -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-5 h-5 mr-2" />
                    Statistik Per Kelas
                </div>
            </x-slot>

            @if (count($statistik_per_kelas) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left rtl:text-right divide-y border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Kelas</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Hadir</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Izin</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Sakit</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Alpha</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($statistik_per_kelas as $kelas)
                                <tr class=" dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $kelas['nama_kelas'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="text-success-600 font-semibold">{{ $kelas['hadir'] }}</span>
                                            <span class="text-xs text-gray-500">({{ $kelas['hadir_persen'] }}%)</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="text-info-600 font-semibold">{{ $kelas['izin'] }}</span>
                                            <span class="text-xs text-gray-500">({{ $kelas['izin_persen'] }}%)</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="text-warning-600 font-semibold">{{ $kelas['sakit'] }}</span>
                                            <span class="text-xs text-gray-500">({{ $kelas['sakit_persen'] }}%)</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="text-danger-600 font-semibold">{{ $kelas['alpha'] }}</span>
                                            <span class="text-xs text-gray-500">({{ $kelas['alpha_persen'] }}%)</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">{{ $kelas['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <x-filament::icon icon="heroicon-o-academic-cap" class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada data kelas untuk ditampilkan</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Pilih periode atau kelas yang berbeda</p>
                </div>
            @endif
        </x-filament::section>

        <!-- Statistik Temporal -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5 mr-2" />
                    Statistik {{ count($statistik_bulanan) > 6 ? 'Bulanan' : 'Harian' }}
                </div>
            </x-slot>

            @if (count($statistik_bulanan) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left rtl:text-right divide-y border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Periode</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Hadir</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Izin</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Sakit</th>
                                <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Alpha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($statistik_bulanan as $periode)
                                <tr class=" dark:hover:bg-gray-800">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $periode['label'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2 dark:bg-gray-700">
                                                <div class="bg-success-600 h-2 rounded-full transition-all duration-300"
                                                     style="width: {{ min(($periode['hadir_persen'] ?? 0), 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ $periode['hadir'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2 dark:bg-gray-700">
                                                <div class="bg-info-600 h-2 rounded-full transition-all duration-300"
                                                     style="width: {{ min(($periode['izin_persen'] ?? 0), 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ $periode['izin'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2 dark:bg-gray-700">
                                                <div class="bg-warning-600 h-2 rounded-full transition-all duration-300"
                                                     style="width: {{ min(($periode['sakit_persen'] ?? 0), 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ $periode['sakit'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <div class="w-16 bg-gray-200 rounded-full h-2 mr-2 dark:bg-gray-700">
                                                <div class="bg-danger-600 h-2 rounded-full transition-all duration-300"
                                                     style="width: {{ min(($periode['alpha_persen'] ?? 0), 100) }}%"></div>
                                            </div>
                                            <span class="text-sm font-medium">{{ $periode['alpha'] }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada data periode untuk ditampilkan</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Pilih rentang tanggal yang berbeda</p>
                </div>
            @endif
        </x-filament::section>
    </div>

    <!-- Persentase Kehadiran Siswa -->
    <x-filament::section class="mt-8">
        <x-slot name="heading">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <x-filament::icon icon="heroicon-o-users" class="w-5 h-5 mr-2" />
                    Persentase Kehadiran Siswa
                </div>
                @if (count($siswa_persentase) > 0)
                    <div class="text-sm text-gray-500">
                        Total: {{ count($siswa_persentase) }} siswa
                    </div>
                @endif
            </div>
        </x-slot>

        @if (count($siswa_persentase) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left rtl:text-right divide-y border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800">
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Nama Siswa</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Kelas</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">% Kehadiran</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Hadir</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Izin</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Sakit</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Alpha</th>
                            <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">Total Hari</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($siswa_persentase as $siswa)
                            <tr class=" dark:hover:bg-gray-800">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $siswa['nama'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $siswa['kelas'] }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="w-20 bg-gray-200 rounded-full h-2 mr-3 dark:bg-gray-700">
                                            <div class="h-2 rounded-full transition-all duration-300
                                                @if($siswa['kehadiran'] >= 90) bg-success-600
                                                @elseif($siswa['kehadiran'] >= 75) bg-warning-600
                                                @else bg-danger-600 @endif"
                                                 style="width: {{ min($siswa['kehadiran'], 100) }}%"></div>
                                        </div>
                                        <span class="font-semibold text-sm
                                            @if($siswa['kehadiran'] >= 90) text-success-600
                                            @elseif($siswa['kehadiran'] >= 75) text-warning-600
                                            @else text-danger-600 @endif">
                                            {{ $siswa['kehadiran'] }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-success-600 font-medium">{{ $siswa['hadir'] }}</td>
                                <td class="px-4 py-3 text-info-600 font-medium">{{ $siswa['izin'] }}</td>
                                <td class="px-4 py-3 text-warning-600 font-medium">{{ $siswa['sakit'] }}</td>
                                <td class="px-4 py-3 text-danger-600 font-medium">{{ $siswa['alpha'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $siswa['total_hari'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center">
                <x-filament::icon icon="heroicon-o-users" class="w-12 h-12 mx-auto text-gray-400 mb-4" />
                <p class="text-gray-500 dark:text-gray-400">Tidak ada data siswa untuk ditampilkan</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Pastikan ada data presensi dalam periode yang dipilih</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
