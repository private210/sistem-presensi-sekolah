<x-filament-panels::page>
    {{-- Holiday Alert --}}
    @if($is_holiday)
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg" style="width: 35%; background: linear-gradient(135deg, #ea666632 0%, #c1323294 100%);">
            <div class="flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-semibold text-red-800 mb-2">🏮 Informasi Hari Libur</h3>
                    <ul class="text-sm text-red-700 space-y-1">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>{{ $holiday_info?->nama_hari_libur }}</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>{{ $holiday_info?->keterangan }}</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>✈️  Presensi tidak dapat dilakukan pada hari libur resmi</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Weekend Alert --}}
    @if($is_weekend && !$is_holiday)
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg" style="width: 40%; background: linear-gradient(135deg, #667eea32 0%, #764ba294 100%);">
            <div class="flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-md font-semibold text-blue-800 mb-2">🏠 Informasi Akhir Pekan</h3>
                    <ul class="text-md text-blue-700 space-y-1">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span> 🗓️ {{ $weekend_info?->nama_hari_libur }}</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>📌 {{ $weekend_info?->keterangan }}</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-blue-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span> ❌ Presensi tidak dilakukan pada akhir pekan</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- School Day Status --}}
    @if(!$is_non_school_day)
        <div class="mb-4 p-4 bg-green-50 border border-dashed border-green-200 rounded-lg" style="width: 45%; background: linear-gradient(135deg, #667eea32 0%, #764ba294 100%);">
            <div class="flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-md font-semibold text-green-800 mb-2">📚 Informasi Hari Sekolah</h3>
                    <ul class="text-md text-green-700 space-y-1">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span> ✅  Hari sekolah aktif - Presensi dapat dilakukan</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0 text-md"></span>
                            <span>🕖 Pertemuan ke-{{ $pertemuan_ke }}</span>
                        </li>
                        @if(Carbon\Carbon::parse($tanggal)->isToday())
                            <li class="flex items-start">
                                <span class="w-2 h-2 bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0 text-md"></span>
                                <span>🗓️ Hari ini: {{ Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</span>
                            </li>
                        @else
                            <li class="flex items-start">
                                <span class="w-2 h-2 bg-green-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                <x-filament::icon name="heroicon-s-calendar" class="w-4 h-4 text-green-600 mr-2" />
                                <span>🗓️ Tanggal: {{ Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Form --}}
    <div class="form-section">
        {{ $this->form }}
    </div>

    {{-- Main Table --}}
    <div class="mt-4">
        {{ $this->table }}
    </div>

    {{-- No Class Assignment Alert --}}
    @if(!$kelas_info)
        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg" style="background: linear-gradient(135deg, #fbbf2432 0%, #f59e0b94 100%);">
            <div class="flex items-start">
                <div class="flex-shrink-0 mt-0.5">
                    <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                        {{-- <svg class="h-4 w-4 text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg> --}}
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-semibold text-yellow-800 mb-2">⚠️ Informasi Penugasan Kelas</h3>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-yellow-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>Anda belum ditugaskan sebagai wali kelas</span>
                        </li>
                        <li class="flex items-start">
                            <span class="w-2 h-2 bg-yellow-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                            <span>Hubungi administrator untuk mendapatkan akses kelas</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
