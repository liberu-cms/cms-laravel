<?php

namespace App\Providers\Filament;

use App\Filament\Resources\MenuItemResource;
use App\Filament\Resources\MenuResource;
use App\Http\Middleware\SetPermissionsTeam;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Role;
use App\Models\Team;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Biostate\FilamentMenuBuilder\FilamentMenuBuilderPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Jetstream\JetstreamPlugin;
use Filament\Jetstream\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('/app')
            ->viteTheme('resources/css/filament/app/theme.css')
            ->colors(['primary' => Color::Gray])
            ->brandLogo('https://laravel.com/img/logomark.min.svg')
            ->brandLogoHeight('40px')
            ->login()
            ->registration()
            ->passwordReset()
            // ->emailVerification()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            // ->topNavigation()
            ->pages([
                Dashboard::class,
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
                ])
            ->tenantMiddleware([
                SetPermissionsTeam::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                JetstreamPlugin::make()
                    ->profilePhoto()
                    ->deleteAccount()
                    ->updatePassword()
                    ->profileInformation()
                    ->logoutBrowserSessions()
                    ->twoFactorAuthentication()
                    ->apiTokens()
                    ->teams(
                        condition: fn() => env('MULTITENANCY', false)
                    )
                    ->configureTeamModels(
                        teamModel: Team::class,
                        roleModel: Role::class,
                    ),
                FilamentShieldPlugin::make()
                    ->navigationGroup("Administration"),
                FilamentMenuBuilderPlugin::make()
                    ->usingMenuModel(Menu::class)
                    ->usingMenuItemModel(MenuItem::class)
                    ->usingMenuResource(MenuResource::class)
                    ->usingMenuItemResource(MenuItemResource::class),
            ]);
    }

    public function boot(): void
    {
        // \Illuminate\Support\Facades\Gate::policy(\Filament\Jetstream\Models\Team::class, \Filament\Jetstream\Policies\TeamPolicy::class);
    }
}
