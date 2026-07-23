<?php

namespace App\Providers;

use App\Support\FilamentTenantResolver;
use Illuminate\Support\ServiceProvider;
use Liberu\Cms\Contracts\Tenancy\TenantModelResolverInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(TenantModelResolverInterface::class, FilamentTenantResolver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
