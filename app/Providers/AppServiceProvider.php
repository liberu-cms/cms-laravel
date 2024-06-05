<?php

/**
 * AppServiceProvider class.
 *
 * Used for application-level service registration and bootstrapping services, including setting up model event listeners.
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Page;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Page::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = \Str::slug($page->title);
            }
        });
    }
}
