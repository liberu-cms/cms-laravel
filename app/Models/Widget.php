<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'widget_area',
        'content',
        'settings',
        'order',
        'is_active',
        'theme_id',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    public function render()
    {
        $widgetClass = $this->getWidgetClass();

        if (class_exists($widgetClass)) {
            $widget = new $widgetClass($this);
            return $widget->render();
        }

        return $this->renderDefault();
    }

    protected function getWidgetClass()
    {
        $type = ucfirst(camel_case($this->type));
        return "App\\Widgets\\{$type}Widget";
    }

    protected function renderDefault()
    {
        return view('widgets.default', [
            'widget' => $this,
            'title' => $this->title,
            'content' => $this->content,
            'settings' => $this->settings ?? []
        ])->render();
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

    public static function getByArea($area, $themeId = null)
    {
        $query = static::where('widget_area', $area)
            ->where('is_active', true)
            ->orderBy('order');

        if ($themeId) {
            $query->where('theme_id', $themeId);
        }

        return $query->get();
    }
}