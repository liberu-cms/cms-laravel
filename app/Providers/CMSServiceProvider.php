<?php

namespace App\Providers;

use Exception;
use Log;
use Illuminate\Support\ServiceProvider;
use App\Services\PluginManager;
use App\Services\ThemeManager;
use App\Services\SEOService;
use App\Models\Theme;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class CMSServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register CMS services
        $this->app->singleton(PluginManager::class);
        $this->app->singleton(ThemeManager::class);
        $this->app->singleton(SEOService::class);
    }

    public function boot()
    {
        // Load active plugins
        $this->loadActivePlugins();

        // Load active theme
        $this->loadActiveTheme();

        // Register Blade directives
        $this->registerBladeDirectives();

        // Register view composers
        $this->registerViewComposers();
    }

    protected function loadActivePlugins()
    {
        try {
            $pluginManager = $this->app->make(PluginManager::class);
            $pluginManager->loadActivePlugins();
        } catch (Exception $e) {
            // Log error but don't break the application
            Log::warning('Failed to load plugins: ' . $e->getMessage());
        }
    }

    protected function loadActiveTheme()
    {
        try {
            $themeManager = $this->app->make(ThemeManager::class);
            $themeManager->loadActiveTheme();
        } catch (Exception $e) {
            // Log error but don't break the application
            Log::warning('Failed to load theme: ' . $e->getMessage());
        }
    }

    protected function registerBladeDirectives()
    {
        // Shortcode directive
        Blade::directive('shortcode', function ($expression) {
            return "<?php echo app('shortcode')->render($expression); ?>";
        });

        // Widget area directive
        Blade::directive('widget_area', function ($expression) {
            return "<?php echo app('widget')->renderArea($expression); ?>";
        });

        // SEO meta directive
        Blade::directive('seo_meta', function ($expression) {
            return "<?php echo app('seo')->renderMetaTags($expression); ?>";
        });

        // Theme asset directive
        Blade::directive('theme_asset', function ($expression) {
            $activeTheme = Theme::getActiveTheme();
            if ($activeTheme) {
                return "<?php echo asset('themes/{$activeTheme->slug}/' . $expression); ?>";
            }
            return "<?php echo asset($expression); ?>";
        });
    }

    protected function registerViewComposers()
    {
        // Share active theme with all views
        View::composer('*', function ($view) {
            $activeTheme = Theme::getActiveTheme();
            $view->with('activeTheme', $activeTheme);
        });

        // Share SEO data with layout views
        View::composer(['layouts.*', 'theme::layouts.*'], function ($view) {
            $seoService = app(SEOService::class);

            // Get current content if available
            $content = $view->getData()['content'] ?? null;

            if ($content) {
                $metaTags = $seoService->generateMetaTags($content);
                $structuredData = $seoService->generateStructuredData($content);

                $view->with([
                    'metaTags' => $metaTags,
                    'structuredData' => $structuredData,
                ]);
            }
        });
    }
}