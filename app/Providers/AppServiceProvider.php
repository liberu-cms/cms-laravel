<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        //
    }
}
use App\Models\Page;

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
