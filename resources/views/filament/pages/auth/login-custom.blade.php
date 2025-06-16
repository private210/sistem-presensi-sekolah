{{-- resources/views/filament/pages/auth/login-custom.blade.php --}}
<x-filament-panels::page.simple>
    {{-- Custom CSS for compact login page --}}
    @push('styles')
    <style>
        /* Override Filament's default background for both light and dark mode */
        html, body {
            background: transparent !important;
        }

        /* Remove background from all Filament containers */
        .fi-body,
        .min-h-screen,
        .fi-simple-layout,
        .fi-simple-page,
        .dark .fi-body,
        .dark .min-h-screen,
        .dark .fi-simple-layout,
        .dark .fi-simple-page {
            background: transparent !important;
        }

        /* Light mode gradient background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #405d89b0, #3d6aa0, #6274c3, #7f98bb);
            /* background: linear-gradient(45deg, #FFF2E0, #C0C9EE, #A2AADB, #898AC4); */
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -1;
        }

        /* Dark mode gradient background */
        .dark body::before {
            background: linear-gradient(45deg, #1a1b3d, #292953, #302c66, #352a6a);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Pattern overlay */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            z-index: -1;
            pointer-events: none;
            /* background-image:
                url('data:image/svg+xml;utf8,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><path d="M30 10L10 20L30 30L50 20L30 10Z" fill="white" opacity="0.3"/></svg>'),
                url('data:image/svg+xml;utf8,<svg width="40" height="40" xmlns="http://www.w3.org/2000/svg"><circle cx="20" cy="20" r="2" fill="white" opacity="0.5"/></svg>'); */
            background-position: 0 0, 30px 30px;
            background-size: 60px 60px, 40px 40px;
        }

        /* Main container */
        .fi-simple-main-ctn {
            position: relative;
            z-index: 10;
        }

        /* Form card with glassmorphism - Light mode - COMPACT */
        .fi-simple-main {
            background: rgb(255, 255, 255) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border-radius: 24px !important;
            box-shadow:
                0 15px 35px rgba(0, 0, 0, 0.1),
                0 5px 15px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.6) !important;
            padding: 1rem !important;
            max-height:90vh !important;
            margin: 0 auto !important;
        }

        /* Form card - Dark mode */
        .dark .fi-simple-main {
            background: rgba(30, 30, 30, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow:
                0 15px 35px rgba(0, 0, 0, 0.3),
                0 5px 15px rgba(0, 0, 0, 0.2) !important;
        }

        /* Simplify form container */
        .fi-simple-main .fi-simple-main-ctn {
            padding: 0 !important;
        }

        /* Remove panel styling */
        .fi-simple-main .fi-panel {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        /* Hide default logo */
        .fi-logo {
            display: none !important;
        }

        /* School branding - COMPACT */
        .school-header {
            text-align: center;
            /* margin-bottom: 1.25rem; Compact: Dari 2rem jadi 1.25rem */
        }

        .school-logo {
            width: 60px;
            height: 60px;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.75rem;
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }

        /* .dark .school-logo {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 100%);
            box-shadow: 0 8px 16px rgba(129, 140, 248, 0.3);
        } */

        .fi-simple-main:hover .school-logo {
            transform: rotate(0deg) scale(1.05);
        }

        .school-logo img {
            width: 50px; /* Compact: Dari 40px jadi 35px */
            height: 50px;
            fill: white;
        }

        .school-name {
            font-size: 1.5rem; /* Compact: Dari 1.75rem jadi 1.5rem */
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            /* margin-bottom: 0.125rem; Compact: Dari 0.25rem jadi 0.125rem */
            /* line-height: 1.2; */
        }

        .dark .school-name {
            background: linear-gradient(135deg, #a5b4fc 0%, #e9d5ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .system-name {
            color: #6b7280;
            font-size: 0.85rem; /* Compact: Dari 0.95rem jadi 0.85rem */
            font-weight: 500;
        }

        .dark .system-name {
            color: #9ca3af;
        }

        .welcome-text {
            text-align: center;
            font-size: 1rem; /* Compact: Dari 1.1rem jadi 1rem */
            color: #4b5563;
            /* margin-bottom: 1.25rem; Compact: Dari 2rem jadi 1.25rem */
            font-weight: 500;
        }

        .dark .welcome-text {
            color: #d1d5db;
        }

        /* Form styling - COMPACT */
        .fi-fo-field-wrp {
            /* margin-bottom: 0.75rem !important; Compact: Dari 1.25rem jadi 0.75rem */
        }

        .fi-fo-field-wrp-label {
            color: #374151 !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important; /* Compact: Dari 0.875rem jadi 0.8rem */
            /* margin-bottom: 0.25rem !important; Compact: Dari 0.5rem jadi 0.25rem */
            display: block !important;
        }

        .dark .fi-fo-field-wrp-label {
            color: #e5e7eb !important;
        }

        /* Simplify input styling - COMPACT */
        .fi-input-wrp {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        .fi-input {
            background-color: rgba(0, 0, 0, 0.05) !important;
            border: 2px solid transparent !important;
            border-radius: 12px !important;
            padding: 0.625rem 0.875rem !important; /* Compact: Dari 0.875rem 1rem jadi 0.625rem 0.875rem */
            font-size: 0.9rem !important; /* Compact: Dari 1rem jadi 0.9rem */
            transition: all 0.2s ease !important;
            width: 100% !important;
            color: #1f2937 !important;
        }

        .dark .fi-input {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 2px solid transparent !important;
            color: #f3f4f6 !important;
        }

        .fi-input:focus {
            background-color: rgba(0, 0, 0, 0.08) !important;
            border-color: #667eea !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .dark .fi-input:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: #818cf8 !important;
        }

        /* Password toggle button styling */
        .fi-input-wrp .fi-btn {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0.5rem !important;
            width: auto !important;
            height: auto !important;
            position: absolute !important;
            right: 0.75rem !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            color: #6b7280 !important;
        }

        .dark .fi-input-wrp .fi-btn {
            color: #9ca3af !important;
        }

        .fi-input-wrp .fi-btn:hover {
            background: transparent !important;
            transform: translateY(-50%) !important;
            box-shadow: none !important;
            color: #4b5563 !important;
        }

        .dark .fi-input-wrp .fi-btn:hover {
            color: #d1d5db !important;
        }

        /* Remember me checkbox - COMPACT */
        .fi-checkbox {
            /* margin-top: 0.5rem !important; Compact: Dari 0.75rem jadi 0.5rem */
        }

        .fi-checkbox-input {
            border-radius: 6px !important;
            cursor: pointer !important;
            width: 1.125rem !important; /* Compact: Dari 1.25rem jadi 1.125rem */
            height: 1.125rem !important;
            background-color: rgba(0, 0, 0, 0.05) !important;
            border: 2px solid transparent !important;
        }

        .dark .fi-checkbox-input {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        .fi-checkbox-input:checked {
            background-color: #667eea !important;
            border-color: #667eea !important;
        }

        .dark .fi-checkbox-input:checked {
            background-color: #818cf8 !important;
            border-color: #818cf8 !important;
        }

        /* Submit button - COMPACT */
        .fi-form-actions {
            /* margin-top: 1rem !important; Compact: Dari 1.5rem jadi 1rem */
        }

        .fi-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 0.625rem !important; /* Compact: Dari 0.875rem jadi 0.625rem */
            font-size: 0.9rem !important; /* Compact: Dari 1rem jadi 0.9rem */
            font-weight: 600 !important;
            width: 100% !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
            color: white !important;
            cursor: pointer !important;
        }

        .dark .fi-btn {
            background: linear-gradient(135deg, #818cf8 0%, #c084fc 100%) !important;
            box-shadow: 0 4px 12px rgba(129, 140, 248, 0.3) !important;
            color: black !important;
        }

        .fi-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4) !important;
        }

        .dark .fi-btn:hover {
            box-shadow: 0 6px 20px rgba(129, 140, 248, 0.4) !important;
        }

        .fi-btn:active {
            transform: translateY(0) !important;
        }

        /* Hide any extra form decorations */
        .fi-fo-actions {
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
        }

        /* Footer - COMPACT */
        .login-footer {
            text-align: center;
            /* margin-top: 1.25rem; Compact: Dari 2rem jadi 1.25rem */
            padding-top: 1rem; /* Compact: Dari 1.5rem jadi 1rem */
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.8rem; /* Compact: Dari 0.875rem jadi 0.8rem */
        }

        .dark .login-footer {
            border-top: 1px solid #374151;
            color: #9ca3af;
        }

        /* Floating decorations */
        .floating-decoration {
            position: fixed;
            font-size: 2rem;
            opacity: 0.15;
            animation: float 20s infinite ease-in-out;
            user-select: none;
            pointer-events: none;
            z-index: 1;
        }

        .dark .floating-decoration {
            opacity: 0.1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(30px) rotate(240deg); }
        }

        .decoration-1 { top: 20%; left: 10%; animation-delay: 0s; }
        .decoration-2 { top: 70%; right: 10%; animation-delay: 5s; }
        .decoration-3 { bottom: 20%; left: 20%; animation-delay: 10s; }
        .decoration-4 { top: 40%; right: 20%; animation-delay: 15s; }

        /* Responsive COMPACT */
        @media (max-width: 640px) {
            .fi-simple-main {
                padding: 1.5rem 1.25rem !important; /* Compact: Dari 2rem 1.5rem */
                margin: 0.75rem !important; /* Compact: Dari 1rem */
            }

            .school-name {
                font-size: 1.25rem; /* Compact: Dari 1.5rem */
            }

            .welcome-text {
                font-size: 0.9rem; /* Compact: Dari 1rem */
                margin-bottom: 1rem; /* Compact: Dari 1.25rem */
            }

            .school-header {
                margin-bottom: 1rem; /* Ultra compact di mobile */
            }

            .fi-fo-field-wrp {
                margin-bottom: 0.5rem !important; /* Ultra compact di mobile */
            }

            .fi-form-actions {
                margin-top: 0.75rem !important; /* Ultra compact di mobile */
            }

            .login-footer {
                margin-top: 1rem; /* Ultra compact di mobile */
                padding-top: 0.75rem;
            }
        }
    </style>
    @endpush

    {{-- Floating decorations --}}
    <div class="floating-decoration decoration-1">📚</div>
    <div class="floating-decoration decoration-2">✏️</div>
    <div class="floating-decoration decoration-3">🎓</div>
    <div class="floating-decoration decoration-4">📐</div>

    {{-- Custom School Header --}}
    <div class="school-header">
        <div class="school-logo">
            <img src="{{ asset('images/LogoSD.png') }}" alt="Logo SDN Banjarejo">
            {{-- Uncomment if you want to use SVG icon
            {{-- <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12,3L1,9L12,15L21,10.09V17H23V9M5,13.18V17.18L12,21L19,17.18V13.18L12,17L5,13.18Z"/>
            </svg> --}}
        </div>
        <h1 class="school-name">SDN Banjarejo</h1>
        <p class="system-name">Sistem Presensi Digital</p>
    </div>

    <h2 class="welcome-text">Selamat Datang! Silakan masuk ke akun Anda</h2>

    {{-- Login Form --}}
    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{-- Footer --}}
    <div class="login-footer">
        <p>© {{ date('Y') }} SDN Banjarejo. Semua hak dilindungi.</p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem;">
            Butuh bantuan? Hubungi admin sekolah
        </p>
    </div>
</x-filament-panels::page.simple>

@push('scripts')
<script>
    // Add parallax effect to floating elements
    document.addEventListener('DOMContentLoaded', function() {
        const decorations = document.querySelectorAll('.floating-decoration');

        document.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;

            decorations.forEach((el, index) => {
                const speed = (index + 1) * 10;
                el.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
            });
        });
    });
</script>
@endpush
