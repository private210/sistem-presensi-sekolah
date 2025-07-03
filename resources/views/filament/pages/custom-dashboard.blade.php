<x-filament-panels::page>
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea27 0%, #764ba242 100%);
            border: 1px solid;
            border-radius: 1rem;
            padding: 2rem;
        }
        .dashboard-header img {
            width: 100px;
            height: auto;
        }

        .dashboard-stats {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .widget-card {
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(81, 3, 3, 0.452);
            overflow: hidden;
        }

        .quick-action {
            border: 2px dashed #cbd5e0;
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .quick-action:hover {
            border-color: #4299e1;
            background: linear-gradient(135deg, #667eea79 0%, #764ba27b 100%);
        }

        .time-info {
            font-size: 0.875rem;
            opacity: 0.8;
            margin-top: 0.5rem;
        }

        .widgets-container {
            display: grid;
            gap: 1.5rem;
        }

        .widget-wrapper {
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1rem;
            }

            .widgets-container {
                grid-template-columns: 1fr;
            }
            .dashboard-header img {
                margin-bottom: 5rem;
            }
        }
    </style>

    <div class="dashboard-header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $this->getCustomHeading() }}</h1>
                <p class="text-lg opacity-90">{{ $this->getCustomSubheading() }}</p>
                <div class="time-info">
                    {{ now()->translatedFormat('l, d F Y') }} • {{ now()->format('H:i') }} WIB
                </div>
            </div>

            <div class="tex-right">
                <img src="{{ asset('images/LogoSD.png') }}" alt="">
            </div>
        </div>
    </div>

    {{-- Quick Actions berdasarkan Role --}}
    @if(auth()->user()->hasRole('Wali Kelas'))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <a href="{{ route('filament.admin.pages.presensi-kelas') }}" class="quick-action">
                <x-heroicon-o-clipboard-document-check class="w-8 h-8 mx-auto mb-2 text-blue-600" />
                <div class="font-semibold">Input Presensi Hari Ini</div>
                <div class="text-sm text-gray-600">Catat kehadiran siswa</div>
            </a>
            <a href="{{ route('filament.admin.pages.rekap-wali-kelas') }}" class="quick-action">
                <x-heroicon-o-chart-bar class="w-8 h-8 mx-auto mb-2 text-green-600" />
                <div class="font-semibold">Lihat Rekap</div>
                <div class="text-sm text-gray-600">Rekap presensi kelas</div>
            </a>
            <a href="{{ route('filament.admin.pages.konfirmasi-izin-wali-kelas') }}" class="quick-action">
                <x-heroicon-o-inbox-arrow-down class="w-8 h-8 mx-auto mb-2 text-green-600" />
                <div class="font-semibold">Lihat dan Konfirmasi Surat Izin</div>
                <div class="text-sm text-gray-600">Konfirmasi Surat Izin siswa</div>
            </a>
        </div>
    @elseif(auth()->user()->hasRole('Wali Murid'))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <a href="{{ route('filament.admin.pages.pengajuan-izin-wali-murid') }}" class="quick-action">
                <x-heroicon-o-plus-circle class="w-8 h-8 mx-auto mb-2 text-blue-600" />
                <div class="font-semibold">Ajukan Izin</div>
                <div class="text-sm text-gray-600">Izin sakit atau keperluan lainnya</div>
            </a>
            <a href="{{ route('filament.admin.pages.rekap-wali-murid') }}" class="quick-action">
                <x-heroicon-o-document-text class="w-8 h-8 mx-auto mb-2 text-green-600" />
                <div class="font-semibold">Rekap Kehadiran</div>
                <div class="text-sm text-gray-600">Lihat riwayat kehadiran anak</div>
            </a>
        </div>
    @elseif(auth()->user()->hasRole('Kepala Sekolah'))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <a href="{{ route('filament.admin.pages.rekap-kepala-sekolah') }}" class="quick-action">
                <x-heroicon-o-presentation-chart-line class="w-8 h-8 mx-auto mb-2 text-blue-600" />
                <div class="font-semibold">Rekap Presensi Sekolah</div>
                <div class="text-sm text-gray-600">Lihat Rekap Presensi Sekolah Keseluruhan</div>
            </a>

            <a href="{{ route('filament.admin.resources.izins.index') }}" class="quick-action">
                <x-heroicon-o-chart-bar class="w-8 h-8 mx-auto mb-2 text-green-600" />
                <div class="font-semibold">Surat Izin</div>
                <div class="text-sm text-gray-600">Lihat riwayat surat izin</div>
            </a>
        </div>
    @endif

    {{-- Widgets Container - Menggunakan Filament's built-in widget rendering --}}
    <div class="widgets-container">
        @php
        $widgetData = $this->getWidgetData();
        @endphp

        @if(!empty($widgetData))
        @foreach($widgetData as $widget)
        <div class="widget-wrapper">
            @if(isset($widget['widget']) && $widget['widget'])
            @livewire($widget['class'])
                @else
                    <div class="p-4 border-l-4 border-red-500 bg-red-50">
                        <h3 class="text-red-800 font-semibold">Error Loading {{ $widget['name'] }}</h3>
                        <p class="text-red-600 text-sm mt-1">Widget tidak dapat dimuat</p>
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="col-span-full text-center py-8">
            <div class="text-gray-500">
                <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto mb-4 opacity-50" />
                <p>Tidak ada widget yang tersedia untuk role Anda.</p>
            </div>
        </div>
        @endif
    </div>

    @if(false)
        <div class="dashboard-stats">
            {!! $this->getRenderedWidgets() !!}
        </div>
    @endif

    {{-- Footer Info --}}
    <div class="mt-8 p-4 bg-gray-50 rounded-lg" style="background: linear-gradient(135deg, #667eea27 0%, #764ba242 100%);">
        <div class="flex items-center justify-between text-sm ">
            <div>
                <strong>Sistem Presensi Sekolah</strong> •
                Terakhir diperbarui: {{ now()->format('d M Y, H:i') }} WIB
            </div>
            <div class="flex items-center space-x-4">
                @if(auth()->user()->hasRole('Wali Kelas'))
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">Wali Kelas</span>
                @elseif(auth()->user()->hasRole('Wali Murid'))
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Wali Murid</span>
                @elseif(auth()->user()->hasRole('Kepala Sekolah'))
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded">Kepala Sekolah</span>
                @elseif(auth()->user()->hasRole('super_admin'))
                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Super Admin</span>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
