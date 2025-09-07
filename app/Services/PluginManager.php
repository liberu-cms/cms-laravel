<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class PluginManager
{
    protected $pluginsPath;

    public function __construct()
    {
        $this->pluginsPath = base_path('plugins');

        if (!File::exists($this->pluginsPath)) {
            File::makeDirectory($this->pluginsPath, 0755, true);
        }
    }

    public function discoverPlugins()
    {
        $discovered = [];
        $pluginDirs = File::directories($this->pluginsPath);

        foreach ($pluginDirs as $pluginDir) {
            $pluginSlug = basename($pluginDir);
            $manifestPath = $pluginDir . '/plugin.json';

            if (File::exists($manifestPath)) {
                $manifest = json_decode(File::get($manifestPath), true);

                if ($manifest && $this->validateManifest($manifest)) {
                    $discovered[] = array_merge($manifest, [
                        'slug' => $pluginSlug,
                        'path' => $pluginDir
                    ]);
                }
            }
        }

        return $discovered;
    }

    public function installPlugin($pluginData)
    {
        try {
            // Check if plugin already exists
            $existingPlugin = Plugin::where('slug', $pluginData['slug'])->first();

            if ($existingPlugin) {
                throw new \Exception("Plugin '{$pluginData['slug']}' already exists.");
            }

            // Validate compatibility
            if (!$this->checkCompatibility($pluginData)) {
                throw new \Exception("Plugin '{$pluginData['slug']}' is not compatible with current system.");
            }

            // Create plugin record
            $plugin = Plugin::create([
                'name' => $pluginData['name'],
                'slug' => $pluginData['slug'],
                'description' => $pluginData['description'] ?? '',
                'version' => $pluginData['version'],
                'author' => $pluginData['author'] ?? '',
                'author_uri' => $pluginData['author_uri'] ?? '',
                'plugin_uri' => $pluginData['plugin_uri'] ?? '',
                'is_active' => false,
                'dependencies' => $pluginData['dependencies'] ?? [],
                'min_php_version' => $pluginData['min_php_version'] ?? null,
                'min_cms_version' => $pluginData['min_cms_version'] ?? null,
                'namespace' => $pluginData['namespace'] ?? '',
                'main_file' => $pluginData['main_file'] ?? 'plugin.php',
                'hooks' => $pluginData['hooks'] ?? [],
                'shortcodes' => $pluginData['shortcodes'] ?? [],
            ]);

            // Run installation migrations if they exist
            $this->runPluginMigrations($plugin, 'install');

            // Run installation hook
            $plugin->install();

            Log::info("Plugin '{$plugin->name}' installed successfully.");

            return $plugin;

        } catch (\Exception $e) {
            Log::error("Failed to install plugin: " . $e->getMessage());
            throw $e;
        }
    }

    public function uploadAndInstallPlugin($zipFile)
    {
        try {
            $tempDir = storage_path('app/temp/plugin_' . uniqid());
            File::makeDirectory($tempDir, 0755, true);

            // Extract ZIP file
            $zip = new ZipArchive;
            if ($zip->open($zipFile) === TRUE) {
                $zip->extractTo($tempDir);
                $zip->close();
            } else {
                throw new \Exception('Failed to extract plugin ZIP file.');
            }

            // Find plugin manifest
            $manifestPath = $this->findPluginManifest($tempDir);
            if (!$manifestPath) {
                throw new \Exception('Plugin manifest (plugin.json) not found.');
            }

            $manifest = json_decode(File::get($manifestPath), true);
            if (!$manifest || !$this->validateManifest($manifest)) {
                throw new \Exception('Invalid plugin manifest.');
            }

            // Move plugin to plugins directory
            $pluginSlug = $manifest['slug'];
            $pluginPath = $this->pluginsPath . '/' . $pluginSlug;

            if (File::exists($pluginPath)) {
                throw new \Exception("Plugin directory '{$pluginSlug}' already exists.");
            }

            File::moveDirectory(dirname($manifestPath), $pluginPath);

            // Install plugin
            $pluginData = array_merge($manifest, [
                'slug' => $pluginSlug,
                'path' => $pluginPath
            ]);

            $plugin = $this->installPlugin($pluginData);

            // Cleanup temp directory
            File::deleteDirectory($tempDir);

            return $plugin;

        } catch (\Exception $e) {
            // Cleanup on failure
            if (isset($tempDir) && File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }

            throw $e;
        }
    }

    public function uninstallPlugin($pluginSlug)
    {
        try {
            $plugin = Plugin::where('slug', $pluginSlug)->firstOrFail();

            // Deactivate if active
            if ($plugin->is_active) {
                $this->deactivatePlugin($pluginSlug);
            }

            // Run uninstallation migrations
            $this->runPluginMigrations($plugin, 'uninstall');

            // Run uninstallation hook
            $plugin->uninstall();

            // Remove plugin files
            $pluginPath = $this->pluginsPath . '/' . $pluginSlug;
            if (File::exists($pluginPath)) {
                File::deleteDirectory($pluginPath);
            }

            Log::info("Plugin '{$plugin->name}' uninstalled successfully.");

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to uninstall plugin: " . $e->getMessage());
            throw $e;
        }
    }

    public function activatePlugin($pluginSlug)
    {
        try {
            $plugin = Plugin::where('slug', $pluginSlug)->firstOrFail();

            // Check dependencies
            if (!$plugin->validateDependencies()) {
                throw new \Exception("Plugin dependencies not met.");
            }

            // Check compatibility
            if (!$plugin->checkCompatibility()) {
                throw new \Exception("Plugin not compatible with current system.");
            }

            // Load plugin
            if (!$plugin->loadPlugin()) {
                throw new \Exception("Failed to load plugin main file.");
            }

            // Activate plugin
            $plugin->activate();

            Log::info("Plugin '{$plugin->name}' activated successfully.");

            return $plugin;

        } catch (\Exception $e) {
            Log::error("Failed to activate plugin: " . $e->getMessage());
            throw $e;
        }
    }

    public function deactivatePlugin($pluginSlug)
    {
        try {
            $plugin = Plugin::where('slug', $pluginSlug)->firstOrFail();
            $plugin->deactivate();

            Log::info("Plugin '{$plugin->name}' deactivated successfully.");

            return $plugin;

        } catch (\Exception $e) {
            Log::error("Failed to deactivate plugin: " . $e->getMessage());
            throw $e;
        }
    }

    public function loadActivePlugins()
    {
        $activePlugins = Plugin::getActivePlugins();

        foreach ($activePlugins as $plugin) {
            try {
                $plugin->loadPlugin();
            } catch (\Exception $e) {
                Log::error("Failed to load plugin '{$plugin->slug}': " . $e->getMessage());
            }
        }
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

    protected function checkCompatibility($pluginData)
    {
        // Check PHP version
        if (isset($pluginData['min_php_version'])) {
            if (version_compare(PHP_VERSION, $pluginData['min_php_version'], '<')) {
                return false;
            }
        }

        // Check CMS version
        if (isset($pluginData['min_cms_version'])) {
            $cmsVersion = config('app.version', '1.0.0');
            if (version_compare($cmsVersion, $pluginData['min_cms_version'], '<')) {
                return false;
            }
        }

        return true;
    }

    protected function findPluginManifest($directory)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->getFilename() === 'plugin.json') {
                return $file->getPathname();
            }
        }

        return null;
    }

    protected function runPluginMigrations($plugin, $action = 'install')
    {
        $migrationsPath = $plugin->getPluginPath() . '/database/migrations';

        if (File::exists($migrationsPath)) {
            try {
                if ($action === 'install') {
                    Artisan::call('migrate', [
                        '--path' => 'plugins/' . $plugin->slug . '/database/migrations',
                        '--force' => true
                    ]);
                } elseif ($action === 'uninstall') {
                    // Run rollback migrations
                    Artisan::call('migrate:rollback', [
                        '--path' => 'plugins/' . $plugin->slug . '/database/migrations',
                        '--force' => true
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning("Plugin migration failed for '{$plugin->slug}': " . $e->getMessage());
            }
        }
    }
}