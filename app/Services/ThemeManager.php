<?php

namespace App\Services;

use Exception;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use App\Models\Theme;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ThemeManager
{
    protected $themesPath;

    public function __construct()
    {
        $this->themesPath = resource_path('themes');

        if (!File::exists($this->themesPath)) {
            File::makeDirectory($this->themesPath, 0755, true);
        }
    }

    public function discoverThemes()
    {
        $discovered = [];
        $themeDirs = File::directories($this->themesPath);

        foreach ($themeDirs as $themeDir) {
            $themeSlug = basename($themeDir);
            $manifestPath = $themeDir . '/theme.json';

            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);

                if ($manifest && $this->validateManifest($manifest)) {
                    $discovered[] = array_merge($manifest, [
                        'slug' => $themeSlug,
                        'path' => $themeDir
                    ]);
                }
            }
        }

        return $discovered;
    }

    public function installTheme($themeData)
    {
        try {
            // Check if theme already exists
            $existingTheme = Theme::where('slug', $themeData['slug'])->first();

            if ($existingTheme) {
                throw new Exception("Theme '{$themeData['slug']}' already exists.");
            }

            // Create theme record
            $theme = Theme::create([
                'name' => $themeData['name'],
                'slug' => $themeData['slug'],
                'description' => $themeData['description'] ?? '',
                'version' => $themeData['version'],
                'author' => $themeData['author'] ?? '',
                'author_uri' => $themeData['author_uri'] ?? '',
                'theme_uri' => $themeData['theme_uri'] ?? '',
                'screenshot' => $themeData['screenshot'] ?? 'screenshot.png',
                'is_active' => false,
                'template_parts' => $themeData['template_parts'] ?? [],
                'color_scheme' => $themeData['color_scheme'] ?? [],
                'typography' => $themeData['typography'] ?? [],
                'layout_options' => $themeData['layout_options'] ?? [],
                'widget_areas' => $themeData['widget_areas'] ?? [],
            ]);

            Log::info("Theme '{$theme->name}' installed successfully.");

            return $theme;

        } catch (Exception $e) {
            Log::error("Failed to install theme: " . $e->getMessage());
            throw $e;
        }
    }

    public function uploadAndInstallTheme($zipFile)
    {
        try {
            $tempDir = storage_path('app/temp/theme_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            // Extract ZIP file
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $zip->extractTo($tempDir);
                $zip->close();
            } else {
                throw new Exception('Failed to extract theme ZIP file.');
            }

            // Find theme manifest
            $manifestPath = $this->findThemeManifest($tempDir);
            if (!$manifestPath) {
                throw new Exception('Theme manifest (theme.json) not found.');
            }

            $manifest = json_decode(File::get($manifestPath), true);
            if (!$manifest || !$this->validateManifest($manifest)) {
                throw new Exception('Invalid theme manifest.');
            }

            // Move theme to themes directory
            $themeSlug = $manifest['slug'];
            $themePath = $this->themesPath . '/' . $themeSlug;

            if (File::exists($themePath)) {
                throw new Exception("Theme directory '{$themeSlug}' already exists.");
            }

            File::moveDirectory(dirname($manifestPath), $themePath);

            // Install theme
            $themeData = array_merge($manifest, [
                'slug' => $themeSlug,
                'path' => $themePath
            ]);

            $theme = $this->installTheme($themeData);

            // Cleanup temp directory
            File::deleteDirectory($tempDir);

            return $theme;

        } catch (Exception $e) {
            // Cleanup on failure
            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            throw $e;
        }
    }

    public function activateTheme($themeSlug)
    {
        try {
            $theme = Theme::where('slug', $themeSlug)->firstOrFail();
            $theme->activate();

            // Register theme views
            $this->registerThemeViews($theme);

            // Generate and cache theme CSS
            $this->generateThemeCSS($theme);

            Log::info("Theme '{$theme->name}' activated successfully.");

            return $theme;

        } catch (Exception $e) {
            Log::error("Failed to activate theme: " . $e->getMessage());
            throw $e;
        }
    }

    public function uninstallTheme($themeSlug)
    {
        try {
            $theme = Theme::where('slug', $themeSlug)->firstOrFail();

            // Cannot uninstall active theme
            if ($theme->is_active) {
                throw new Exception("Cannot uninstall active theme. Please activate another theme first.");
            }

            // Remove theme files
            $themePath = $this->themesPath . '/' . $themeSlug;
            if (File::exists($themePath)) {
                File::deleteDirectory($themePath);
            }

            // Delete theme record
            $theme->delete();

            Log::info("Theme '{$theme->name}' uninstalled successfully.");

            return true;

        } catch (Exception $e) {
            Log::error("Failed to uninstall theme: " . $e->getMessage());
            throw $e;
        }
    }

    public function registerThemeViews($theme)
    {
        $themePath = $theme->getThemePath();

        if (File::exists($themePath)) {
            // Add theme views to view finder
            View::addLocation($themePath);

            // Register theme namespace
            View::addNamespace('theme', $themePath);
        }
    }

    public function generateThemeCSS($theme)
    {
        $css = $theme->generateCSS();

        // Save CSS to public directory
        $cssPath = public_path("themes/{$theme->slug}");
        if (!File::exists($cssPath)) {
            File::makeDirectory($cssPath, 0755, true);
        }

        File::put($cssPath . '/style.css', $css);

        return $css;
    }

    public function customizeTheme($themeSlug, $customizations)
    {
        try {
            $theme = Theme::where('slug', $themeSlug)->firstOrFail();

            // Update color scheme
            if (isset($customizations['colors'])) {
                $theme->color_scheme = array_merge(
                    $theme->getColorOptions(),
                    $customizations['colors']
                );
            }

            // Update typography
            if (isset($customizations['typography'])) {
                $theme->typography = array_merge(
                    $theme->getTypographyOptions(),
                    $customizations['typography']
                );
            }

            // Update layout options
            if (isset($customizations['layout'])) {
                $theme->layout_options = array_merge(
                    $theme->getLayoutOptions(),
                    $customizations['layout']
                );
            }

            // Update custom CSS
            if (isset($customizations['custom_css'])) {
                $theme->custom_css = $customizations['custom_css'];
            }

            // Update custom JS
            if (isset($customizations['custom_js'])) {
                $theme->custom_js = $customizations['custom_js'];
            }

            $theme->save();

            // Regenerate CSS if theme is active
            if ($theme->is_active) {
                $this->generateThemeCSS($theme);
            }

            return $theme;

        } catch (Exception $e) {
            Log::error("Failed to customize theme: " . $e->getMessage());
            throw $e;
        }
    }

    public function getThemeCustomizer($themeSlug)
    {
        $theme = Theme::where('slug', $themeSlug)->firstOrFail();
        return $theme->getCustomizer();
    }

    public function previewTheme($themeSlug, $customizations = [])
    {
        $theme = Theme::where('slug', $themeSlug)->firstOrFail();

        // Apply temporary customizations
        $tempTheme = clone $theme;

        if (isset($customizations['colors'])) {
            $tempTheme->color_scheme = array_merge(
                $tempTheme->getColorOptions(),
                $customizations['colors']
            );
        }

        if (isset($customizations['typography'])) {
            $tempTheme->typography = array_merge(
                $tempTheme->getTypographyOptions(),
                $customizations['typography']
            );
        }

        if (isset($customizations['layout'])) {
            $tempTheme->layout_options = array_merge(
                $tempTheme->getLayoutOptions(),
                $customizations['layout']
            );
        }

        if (isset($customizations['custom_css'])) {
            $tempTheme->custom_css = $customizations['custom_css'];
        }

        return $tempTheme->generateCSS();
    }

    protected function validateManifest($manifest)
    {
        $required = ['name', 'slug', 'version'];

        foreach ($required as $field) {
            if (!isset($manifest[$field]) || empty($manifest[$field])) {
                return false;
            }
        }

        return true;
    }

    protected function findThemeManifest($directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->getFilename() === 'theme.json') {
                return $file->getPathname();
            }
        }

        return null;
    }

    public function loadActiveTheme()
    {
        $activeTheme = Theme::getActiveTheme();

        if ($activeTheme) {
            $this->registerThemeViews($activeTheme);
        }

        return $activeTheme;
    }
}