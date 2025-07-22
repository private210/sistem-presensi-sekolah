<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Pages\CustomDashboard;
use App\Filament\Pages\Auth\LoginCustom;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\MenuItem;
use App\Filament\Widgets\IzinPendingWidget;
use App\Filament\Widgets\SiswaRankingWidget;
use App\Filament\Widgets\ChartPresensiWidget;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Widgets\AktivitasTerbaruWidget;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Widgets\DashboardWaliKelasStats;
use App\Filament\Widgets\DashboardWaliMuridStats;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\Widgets\DashboardKepalaSekolahStats;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(LoginCustom::class)
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                CustomDashboard::class,
                // Pages\Dashboard::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Role-specific stats widgets - akan ditampilkan berdasarkan role user
                // DashboardKepalaSekolahStats::class,
                // DashboardWaliKelasStats::class,
                // DashboardWaliMuridStats::class,

                // // Chart and activity widgets
                // ChartPresensiWidget::class,
                // AktivitasTerbaruWidget::class,
                // IzinPendingWidget::class,
                // SiswaRankingWidget::class,

                // Default widgets (optional)
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->userMenuItems([
                MenuItem::make()
                    ->label('Home')
                    ->url(url('/'))
                    ->icon('heroicon-o-home'),
                // Override logout
                'logout' => MenuItem::make()
                    ->label('Keluar')
                    ->icon('heroicon-o-arrow-left-on-rectangle'),
            ])
            // ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(FilamentShieldPlugin::make());
    }
    public function boot(): void
    {
        // Listen to logout route
        Route::post('/admin/logout', function () {
            Auth::guard('web')->logout(); // Logout user

            request()->session()->invalidate();
            request()->session()->regenerateToken();

            return redirect('/'); // ✅ Redirect ke home
        })->name('filament.admin.logout');
    }
}
