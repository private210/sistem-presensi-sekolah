{{-- resources/views/filament/pages/pengajuan-izin-wali-murid.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Info --}}
        <div class="shadow p-6 rounded-lg" style="background: linear-gradient(135deg, #667eea27 0%, #764ba242 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold dark:text-white">
                        Pengajuan Surat Izin
                    </h2>
                    <p class="dark:text-white mt-1">
                        Ajukan surat izin untuk {{ auth()->user()->waliMurid->siswa->nama_lengkap }}
                        -  {{ auth()->user()->waliMurid->siswa->kelas->nama_kelas }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold dark:text-white">
                        {{ $this->getTableQuery()->where('status', 'Menunggu')->count() }}
                    </div>
                    <div class="text-sm dark:text-white">Menunggu Konfirmasi</div>
                </div>
            </div>
        </div>
         {{-- Info Panel --}}
         <div class=" dark:text-white rounded-lg p-4" style="background: linear-gradient(135deg, #667eea27 0%, #764ba242 100%);">
            <div class="flex items-start">
                {{-- <div class="">
                    <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div> --}}
                <x-filament::icon icon="heroicon-m-information-circle" class="w-10 h-5 text-blue-400 mt-0.5" />
                <div class="ml-3">
                    <h3 class="text-md font-medium text-blue-800">
                        Informasi Penting
                    </h3>
                    <div class="mt-1 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Surat izin harus diajukan minimal H-1 sebelum tanggal izin</li>
                            <li>Untuk izin sakit, disarankan melampirkan surat keterangan dokter</li>
                            <li>Status pengajuan akan diperbarui setelah diproses oleh wali kelas</li>
                            <li>Pengajuan yang sudah disetujui/ditolak tidak dapat diubah</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Pengajuan --}}
        <div class="  rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Form Pengajuan Izin Baru</h3>

            <form wire:submit="create">
                {{ $this->form }}
                <div class="mt-3 flex justify-end">
                   <x-filament::button type="submit" color="primary" class="w-full sm:w-auto">
                        <x-slot name="icon">
                            <x-filament::icon icon="heroicon-m-paper-airplane" class="w-4 h-4" />
                        </x-slot>
                    Ajukan Izin
                    </x-filament::button>
                </div>
            </form>
        </div>

        {{-- Riwayat Pengajuan --}}
          <div class="  rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Riwayat Pengajuan Izin</h3>
                <p class="text-sm text-gray-600 mt-1">Daftar semua pengajuan izin yang pernah diajukan</p>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div wire:loading wire:target="create" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="  rounded-lg p-6 flex items-center space-x-3">
            <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700">Memproses pengajuan...</span>
        </div>
    </div>
</x-filament-panels::page>
