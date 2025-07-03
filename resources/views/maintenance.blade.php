<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'EduPortal') }} - Sedang Maintenance</title>
    <!-- Tailwind CSS via CDN for demo - replace with Vite build -->
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- @vite('resources/css/app.css') --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'edu-blue': '#1e40af',
                        'edu-green': '#059669',
                        'edu-orange': '#ea580c',
                        'edu-purple': '#7c3aed',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                        'bounce-slow': 'bounce 2s infinite',
                        'spin-slow': 'spin 8s linear infinite',
                        'fade-in': 'fadeIn 1s ease-in-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'book-flip': 'bookFlip 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(30px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(50px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 20px rgba(30, 64, 175, 0.5)' },
                            '100%': { boxShadow: '0 0 30px rgba(30, 64, 175, 0.8)' },
                        },
                        bookFlip: {
                            '0%, 100%': { transform: 'rotateY(0deg)' },
                            '50%': { transform: 'rotateY(180deg)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900 overflow-hidden relative">
    <!-- Animated Background Elements - Education Theme -->
    <div class="absolute inset-0 overflow-hidden">
        <!-- Floating Books -->
        <div class="absolute top-10 left-10 w-16 h-20 bg-edu-blue rounded-lg mix-blend-multiply filter blur-xl opacity-70 animate-float"></div>
        <div class="absolute top-40 right-20 w-20 h-24 bg-edu-green rounded-lg mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-20 left-20 w-18 h-22 bg-edu-orange rounded-lg mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 4s;"></div>
        <div class="absolute bottom-40 right-10 w-14 h-18 bg-edu-purple rounded-lg mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 1s;"></div>

        <!-- Educational Icons -->
        <div class="absolute top-1/4 left-1/6 text-white opacity-20 animate-pulse">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 3L1 9L12 15L21 12.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z"/>
            </svg>
        </div>
        <div class="absolute top-1/3 right-1/4 text-white opacity-20 animate-bounce">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3M19 19H5V5H19V19M17 17H7V15H17V17M15 13H7V11H15V13M13 9H7V7H13V9Z"/>
            </svg>
        </div>
        <div class="absolute bottom-1/4 left-1/3 text-white opacity-20 animate-pulse">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.5 3A6.5 6.5 0 0 1 16 9.5C16 11.11 15.41 12.59 14.44 13.73L14.71 14H15.5L20.5 19L19 20.5L14 15.5V14.71L13.73 14.44C12.59 15.41 11.11 16 9.5 16A6.5 6.5 0 0 1 3 9.5A6.5 6.5 0 0 1 9.5 3M9.5 5C7 5 5 7 5 9.5S7 14 9.5 14S14 12 14 9.5S12 5 9.5 5Z"/>
            </svg>
        </div>
    </div>

    <!-- Floating Educational Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-yellow-400 rounded-full animate-pulse opacity-60"></div>
        <div class="absolute top-1/3 right-1/3 w-1 h-1 bg-green-400 rounded-full animate-ping opacity-70"></div>
        <div class="absolute bottom-1/4 left-1/3 w-1.5 h-1.5 bg-orange-400 rounded-full animate-bounce opacity-50"></div>
        <div class="absolute bottom-1/3 right-1/4 w-1 h-1 bg-blue-400 rounded-full animate-pulse opacity-60"></div>
    </div>

    <!-- Main Content -->
    <div class="min-h-screen flex items-center justify-center p-4 relative z-10">
        <div class="max-w-4xl w-full text-center">
            <!-- Logo/Brand Section - Education Theme -->
            <div class="mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-24 h-24 shadow-2xl animate-glow mb-6">
                 <img src="{{ asset('images/LogoSD.png') }}" alt="SDN Banjarejo Logo">
                </div>
            </div>

            <!-- Main Message -->
            <div class="animate-slide-up" style="animation-delay: 0.3s;">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-6 leading-tight">
                    Sistem {{ config('app.name', 'EduPortal') }}Sedang
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500">Diperbaiki</span>
                </h2>
                <p class="text-xl md:text-2xl text-gray-300 mb-8 leading-relaxed max-w-3xl mx-auto">
                    Kami sedang melakukan pemeliharaan sistem presensi untuk memberikan pengalaman presensi yang lebih baik.
                    Proses presensi akan segera kembali normal.
                </p>
            </div>

            <!-- Progress Bar -->
            <div class="animate-slide-up mb-8" style="animation-delay: 0.6s;">
                <div class="max-w-md mx-auto mb-4">
                    <div class="flex justify-between text-sm text-gray-400 mb-2">
                        <span>Progress Pemeliharaan</span>
                        <span id="progress-text">0%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden">
                        <div id="progress-bar" class="h-full bg-gradient-to-r from-edu-blue via-edu-green to-edu-orange rounded-full transition-all duration-1000 ease-out shadow-lg" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- Countdown Timer -->
            <div class="animate-slide-up mb-8" style="animation-delay: 0.9s;">
                <p class="text-lg text-gray-400 mb-4">Estimasi sistem kembali normal dalam:</p>
                <div class="flex justify-center space-x-4" id="countdown">
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4 min-w-[80px] hover:bg-opacity-20 transition-all duration-300">
                        <div class="text-2xl md:text-3xl font-bold text-white" id="days">00</div>
                        <div class="text-sm text-gray-300">Hari</div>
                    </div>
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4 min-w-[80px] hover:bg-opacity-20 transition-all duration-300">
                        <div class="text-2xl md:text-3xl font-bold text-white" id="hours">00</div>
                        <div class="text-sm text-gray-300">Jam</div>
                    </div>
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4 min-w-[80px] hover:bg-opacity-20 transition-all duration-300">
                        <div class="text-2xl md:text-3xl font-bold text-white" id="minutes">00</div>
                        <div class="text-sm text-gray-300">Menit</div>
                    </div>
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4 min-w-[80px] hover:bg-opacity-20 transition-all duration-300">
                        <div class="text-2xl md:text-3xl font-bold text-white" id="seconds">00</div>
                        <div class="text-sm text-gray-300">Detik</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Total maintenance time: 3 days in milliseconds
        const totalMaintenanceTime = 8 * 24 * 60 * 60 * 1000; // 3 days

        // Set target time to 3 days from now
        const now = new Date().getTime();
        const targetTime = now + totalMaintenanceTime;

        // Countdown Timer and Progress Bar
        function updateCountdownAndProgress() {
            const interval = setInterval(function() {
                const currentTime = new Date().getTime();
                const distance = targetTime - currentTime;

                if (distance < 0) {
                    clearInterval(interval);
                    document.getElementById('countdown').innerHTML = '<div class="text-green-400 text-xl font-bold">Sistem Pembelajaran Kembali Normal!</div>';
                    document.getElementById('progress-bar').style.width = '100%';
                    document.getElementById('progress-text').textContent = '100%';
                    return;
                }

                // Calculate time units
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Update countdown display
                document.getElementById('days').textContent = days.toString().padStart(2, '0');
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');

                // Calculate progress percentage (100% - remaining time percentage)
                const remainingTime = distance;
                const elapsedTime = totalMaintenanceTime - remainingTime;
                const progressPercentage = Math.min(100, Math.max(0, (elapsedTime / totalMaintenanceTime) * 100));

                // Update progress bar
                document.getElementById('progress-bar').style.width = progressPercentage + '%';
                document.getElementById('progress-text').textContent = Math.floor(progressPercentage) + '%';
            }, 1000);
        }

        // Initialize countdown and progress
        document.addEventListener('DOMContentLoaded', function() {
            updateCountdownAndProgress();
        });

        // Add interactive mouse tracking
        document.addEventListener('mousemove', function(e) {
            const cursor = document.querySelector('.cursor-glow');
            if (!cursor) {
                const glowDiv = document.createElement('div');
                glowDiv.className = 'cursor-glow fixed w-4 h-4 bg-edu-blue rounded-full pointer-events-none z-50 mix-blend-difference opacity-50';
                document.body.appendChild(glowDiv);
            }

            const glow = document.querySelector('.cursor-glow');
            glow.style.left = e.clientX - 8 + 'px';
            glow.style.top = e.clientY - 8 + 'px';
        });
    </script>
</body>
</html>
