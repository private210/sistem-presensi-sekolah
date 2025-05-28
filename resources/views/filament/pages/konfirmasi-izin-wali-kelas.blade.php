{{-- resources/views/filament/pages/konfirmasi-izin-wali-kelas.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Info --}}
        <div class="  rounded-lg shadow p-6 border border-gray-300" style="background: linear-gradient(135deg, #667eea27 0%, #764ba242 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Konfirmasi Surat Izin Siswa
                    </h2>
                    <p class="text-gray-300 mt-1">
                        Kelola permohonan izin siswa di {{ auth()->user()->waliKelas->kelas->nama_kelas }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ $this->getTableQuery()->where('status', 'Menunggu')->count() }}
                    </div>
                    <div class="text-sm text-gray-300">Menunggu Konfirmasi</div>
                </div>
            </div>
        </div>
        {{-- Table --}}
        <div class="  rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
