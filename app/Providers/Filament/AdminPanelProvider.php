<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Rmsramos\Activitylog\ActivitylogPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use Filament\Navigation\NavigationItem;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->brandLogo(asset('img/logo.png'))
            ->brandLogoHeight('2rem')
            ->authGuard('web')
            ->default()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Orange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->navigationItems([

            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
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
                \Spatie\Csp\AddCspHeaders::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->widgets([
                //
            ])
            ->plugins([
                ActivitylogPlugin::make()
                    ->resource(\App\Filament\Resources\ActivityLogResource::class)
                    ->label('Log')
                    ->pluralLabel('Logs')
                    ->navigationItem(true)
                    ->navigationGroup('Auditoría')
                    ->navigationCountBadge(true),

                \TomatoPHP\FilamentUsers\FilamentUsersPlugin::make(),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                BreezyCore::make()
                    ->enableTwoFactorAuthentication(
                        force: false,
                    )
                    ->enableBrowserSessions(condition: true)
                    ->myProfile(
                        shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                        userMenuLabel: 'Mi Perfil', // Customizes the 'account' link label in the panel User Menu (default = null)
                        shouldRegisterNavigation: true, // Adds a main navigation item for the My Profile page (default = false)
                        navigationGroup: 'Configuración', // Sets the navigation group for the My Profile page (default = null)
                        hasAvatars: true, // Enables the avatar upload form component (default = false)
                        slug: 'my-profile' // Sets the slug for the profile page (default = 'my-profile')
                    )
                    ->customMyProfilePage(\App\Filament\Pages\MyProfileCustomPage::class),

                \Boquizo\FilamentLogViewer\FilamentLogViewerPlugin::make()
                    ->navigationGroup('Configuración')
                    ->navigationSort(3)
                    ->navigationIcon('heroicon-o-bug-ant') // string en v1.x
                    ->navigationLabel('Logs')
                    ->authorize(function (): bool {
                        return auth()->user()->hasAnyRole(['super_admin']);
                    }),

                FilamentSpatieLaravelHealthPlugin::make()
                    ->authorize(function (): bool {
                        return auth()->user()->hasAnyRole(['super_admin']);
                    }),

                ]);
    }
}
