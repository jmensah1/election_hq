<?php

namespace App\Providers\Filament;

use App\Models\Organization;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName(fn () => $this->getBrandName())
            ->brandLogo(fn () => $this->getBrandLogo())
            ->darkModeBrandLogo(fn () => $this->getBrandLogo())
            ->brandLogoHeight('3rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                \App\Http\Middleware\SetOrganizationContext::class,
                \App\Http\Middleware\SetOrganizationTimezone::class,
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
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Horizon')
                    ->url('/horizon', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-globe-alt')
                    ->group('System')
                    ->visible(fn (): bool => auth()->user()?->is_super_admin ?? false),
            ]);
    }

    protected function getOrganizationFromRequest(): ?Organization
    {
        // Try to get from app container first (set by middleware)
        if (function_exists('current_organization') && current_organization()) {
            return current_organization();
        }

        // Fallback: resolve from subdomain directly
        $host = request()->getHost();
        $baseDomain = config('app.base_domain', 'elections-hq.me');
        
        // Check if we're on a subdomain
        if (str_ends_with($host, '.' . $baseDomain)) {
            $subdomain = str_replace('.' . $baseDomain, '', $host);
            
            if ($subdomain && $subdomain !== 'www') {
                return Organization::where('subdomain', $subdomain)->first();
            }
        }

        return null;
    }

    protected function getBrandName(): string
    {
        $org = $this->getOrganizationFromRequest();
        
        if ($org) {
            return $org->name;
        }
        
        return 'Elections HQ';
    }

    protected function getBrandLogo(): ?string
    {
        $org = $this->getOrganizationFromRequest();
        
        if ($org && $org->logo_path) {
            return asset('storage/' . $org->logo_path);
        }
        
        // Return null to use brandName text instead
        return null;
    }
}
