<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'author_uri',
        'plugin_uri',
        'is_active',
        'settings',
        'dependencies',
        'min_php_version',
        'min_cms_version',
        'namespace',
        'main_file',
        'hooks',
        'shortcodes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'dependencies' => 'array',
        'hooks' => 'array',
        'shortcodes' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::saved(function ($plugin) {
            Cache::forget('active_plugins');
            Cache::forget('plugin_hooks');
            Cache::forget('plugin_shortcodes');
        });

        static::deleted(function ($plugin) {
            Cache::forget('active_plugins');
            Cache::forget('plugin_hooks');
            Cache::forget('plugin_shortcodes');
        });
    }

    public function activate()
    {
        $this->is_active = true;
        $this->save();

        // Run activation hook if exists
        $this->runHook('activation');

        return $this;
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();

        // Run deactivation hook if exists
        $this->runHook('deactivation');

        return $this;
    }

    public function install()
    {
        // Run installation hook if exists
        $this->runHook('installation');

        return $this;
    }

    public function uninstall()
    {
        // Run uninstallation hook if exists
        $this->runHook('uninstallation');

        $this->delete();

        return $this;
    }

    public function runHook($hookName, $data = [])
    {
        if (!$this->is_active) {
            return false;
        }

        $hooks = $this->hooks ?? [];

        if (isset($hooks[$hookName])) {
            $hookClass = $hooks[$hookName];

            if (class_exists($hookClass)) {
                $hookInstance = new $hookClass();

                if (method_exists($hookInstance, 'handle')) {
                    return $hookInstance->handle($data);
                }
            }
        }

        return false;
    }

    public function getPluginPath()
    {
        return base_path("plugins/{$this->slug}");
    }

    public function getMainFilePath()
    {
        return $this->getPluginPath() . '/' . $this->main_file;
    }

    public function loadPlugin()
    {
        if (!$this->is_active) {
            return false;
        }

        $mainFile = $this->getMainFilePath();

        if (File::exists($mainFile)) {
            require_once $mainFile;
            return true;
        }

        return false;
    }

    public function validateDependencies()
    {
        $dependencies = $this->dependencies ?? [];

        foreach ($dependencies as $dependency) {
            if (!static::where('slug', $dependency)->where('is_active', true)->exists()) {
                return false;
            }
        }

        return true;
    }

    public function checkCompatibility()
    {
        // Check PHP version
        if ($this->min_php_version && version_compare(PHP_VERSION, $this->min_php_version, '<')) {
            return false;
        }

        // Check CMS version (you can implement this based on your versioning system)
        if ($this->min_cms_version) {
            $cmsVersion = config('app.version', '1.0.0');
            if (version_compare($cmsVersion, $this->min_cms_version, '<')) {
                return false;
            }
        }

        return true;
    }

    public function getSetting($key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();

        return $this;
    }

    public static function getActivePlugins()
    {
        return Cache::remember('active_plugins', 3600, function () {
            return static::where('is_active', true)->get();
        });
    }

    public static function getPluginHooks()
    {
        return Cache::remember('plugin_hooks', 3600, function () {
            $hooks = [];

            static::where('is_active', true)->get()->each(function ($plugin) use (&$hooks) {
                if ($plugin->hooks) {
                    foreach ($plugin->hooks as $hookName => $hookClass) {
                        if (!isset($hooks[$hookName])) {
                            $hooks[$hookName] = [];
                        }
                        $hooks[$hookName][] = [
                            'plugin' => $plugin,
                            'class' => $hookClass
                        ];
                    }
                }
            });

            return $hooks;
        });
    }

    public static function getPluginShortcodes()
    {
        return Cache::remember('plugin_shortcodes', 3600, function () {
            $shortcodes = [];

            static::where('is_active', true)->get()->each(function ($plugin) use (&$shortcodes) {
                if ($plugin->shortcodes) {
                    foreach ($plugin->shortcodes as $shortcode => $shortcodeClass) {
                        $shortcodes[$shortcode] = [
                            'plugin' => $plugin,
                            'class' => $shortcodeClass
                        ];
                    }
                }
            });

            return $shortcodes;
        });
    }
}