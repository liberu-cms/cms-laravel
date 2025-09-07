<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'author_uri',
        'theme_uri',
        'screenshot',
        'is_active',
        'settings',
        'template_parts',
        'custom_css',
        'custom_js',
        'color_scheme',
        'typography',
        'layout_options',
        'widget_areas',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'template_parts' => 'array',
        'color_scheme' => 'array',
        'typography' => 'array',
        'layout_options' => 'array',
        'widget_areas' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::saved(function ($theme) {
            Cache::forget('active_theme');
            Cache::forget('theme_settings');
        });
    }

    public function activate()
    {
        // Deactivate all other themes
        static::where('is_active', true)->update(['is_active' => false]);

        $this->is_active = true;
        $this->save();

        // Clear view cache
        $this->clearViewCache();

        return $this;
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();

        return $this;
    }

    public function getThemePath()
    {
        return resource_path("themes/{$this->slug}");
    }

    public function getThemeUrl()
    {
        return asset("themes/{$this->slug}");
    }

    public function getScreenshotUrl()
    {
        if ($this->screenshot) {
            return $this->getThemeUrl() . '/' . $this->screenshot;
        }

        return asset('images/theme-placeholder.png');
    }

    public function getTemplateFile($template)
    {
        $themePath = $this->getThemePath();
        $templateFile = $themePath . "/templates/{$template}.blade.php";

        if (File::exists($templateFile)) {
            return $templateFile;
        }

        // Fallback to default template
        return resource_path("views/themes/default/templates/{$template}.blade.php");
    }

    public function hasTemplate($template)
    {
        $templateFile = $this->getThemePath() . "/templates/{$template}.blade.php";
        return File::exists($templateFile);
    }

    public function getCustomizer()
    {
        return [
            'colors' => $this->getColorOptions(),
            'typography' => $this->getTypographyOptions(),
            'layout' => $this->getLayoutOptions(),
            'widgets' => $this->getWidgetAreas(),
        ];
    }

    public function getColorOptions()
    {
        return $this->color_scheme ?? [
            'primary' => '#3b82f6',
            'secondary' => '#64748b',
            'accent' => '#f59e0b',
            'background' => '#ffffff',
            'text' => '#1f2937',
            'link' => '#2563eb',
        ];
    }

    public function getTypographyOptions()
    {
        return $this->typography ?? [
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
            'heading_size' => '2rem',
            'body_size' => '1rem',
            'line_height' => '1.6',
        ];
    }

    public function getLayoutOptions()
    {
        return $this->layout_options ?? [
            'container_width' => '1200px',
            'sidebar_position' => 'right',
            'header_style' => 'default',
            'footer_style' => 'default',
        ];
    }

    public function getWidgetAreas()
    {
        return $this->widget_areas ?? [
            'sidebar' => 'Sidebar',
            'footer-1' => 'Footer Column 1',
            'footer-2' => 'Footer Column 2',
            'footer-3' => 'Footer Column 3',
        ];
    }

    public function updateSettings($settings)
    {
        $currentSettings = $this->settings ?? [];
        $this->settings = array_merge($currentSettings, $settings);
        $this->save();

        return $this;
    }

    public function getSetting($key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    public function generateCSS()
    {
        $colors = $this->getColorOptions();
        $typography = $this->getTypographyOptions();
        $layout = $this->getLayoutOptions();

        $css = ":root {\n";

        // Color variables
        foreach ($colors as $name => $value) {
            $css .= "  --color-{$name}: {$value};\n";
        }

        // Typography variables
        $css .= "  --font-heading: '{$typography['heading_font']}';\n";
        $css .= "  --font-body: '{$typography['body_font']}';\n";
        $css .= "  --size-heading: {$typography['heading_size']};\n";
        $css .= "  --size-body: {$typography['body_size']};\n";
        $css .= "  --line-height: {$typography['line_height']};\n";

        // Layout variables
        $css .= "  --container-width: {$layout['container_width']};\n";

        $css .= "}\n\n";

        // Base styles
        $css .= "body {\n";
        $css .= "  font-family: var(--font-body);\n";
        $css .= "  font-size: var(--size-body);\n";
        $css .= "  line-height: var(--line-height);\n";
        $css .= "  color: var(--color-text);\n";
        $css .= "  background-color: var(--color-background);\n";
        $css .= "}\n\n";

        $css .= "h1, h2, h3, h4, h5, h6 {\n";
        $css .= "  font-family: var(--font-heading);\n";
        $css .= "  color: var(--color-text);\n";
        $css .= "}\n\n";

        $css .= "a {\n";
        $css .= "  color: var(--color-link);\n";
        $css .= "}\n\n";

        $css .= ".container {\n";
        $css .= "  max-width: var(--container-width);\n";
        $css .= "  margin: 0 auto;\n";
        $css .= "  padding: 0 1rem;\n";
        $css .= "}\n\n";

        // Add custom CSS
        if ($this->custom_css) {
            $css .= $this->custom_css;
        }

        return $css;
    }

    public function clearViewCache()
    {
        // Clear compiled views
        $viewPath = storage_path('framework/views');
        if (File::exists($viewPath)) {
            File::cleanDirectory($viewPath);
        }

        // Clear cache
        Cache::forget('active_theme');
        Cache::forget('theme_settings');
    }

    public static function getActiveTheme()
    {
        return Cache::remember('active_theme', 3600, function () {
            return static::where('is_active', true)->first();
        });
    }

    public static function getDefaultTheme()
    {
        return static::where('slug', 'default')->first();
    }

    public function widgets()
    {
        return $this->hasMany(Widget::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}