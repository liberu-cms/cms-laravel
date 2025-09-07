<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;

class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'content',
        'settings',
        'is_active',
        'category',
        'description',
        'preview_image',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    // Block type constants
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_GALLERY = 'gallery';
    const TYPE_QUOTE = 'quote';
    const TYPE_CODE = 'code';
    const TYPE_BUTTON = 'button';
    const TYPE_SEPARATOR = 'separator';
    const TYPE_COLUMNS = 'columns';
    const TYPE_ACCORDION = 'accordion';
    const TYPE_TABS = 'tabs';
    const TYPE_SLIDER = 'slider';
    const TYPE_FORM = 'form';
    const TYPE_MAP = 'map';
    const TYPE_SOCIAL = 'social';
    const TYPE_CUSTOM = 'custom';

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contents()
    {
        return $this->morphedByMany(Content::class, 'blockable')
            ->using(Blockable::class)
            ->withPivot('order', 'settings');
    }

    public function render($settings = [])
    {
        $blockSettings = array_merge($this->settings ?? [], $settings);

        $blockClass = $this->getBlockClass();

        if (class_exists($blockClass)) {
            $block = new $blockClass($this, $blockSettings);
            return $block->render();
        }

        return $this->renderDefault($blockSettings);
    }

    protected function getBlockClass()
    {
        $type = ucfirst(camel_case($this->type));
        return "App\\ContentBlocks\\{$type}Block";
    }

    protected function renderDefault($settings = [])
    {
        $viewName = "content-blocks.{$this->type}";

        if (View::exists($viewName)) {
            return view($viewName, [
                'block' => $this,
                'content' => $this->content,
                'settings' => $settings,
            ])->render();
        }

        // Fallback to basic rendering
        return "<div class='content-block content-block-{$this->type}'>{$this->content}</div>";
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

    public function getPreviewHtml()
    {
        return $this->render();
    }

    public static function getAvailableTypes()
    {
        return [
            self::TYPE_TEXT => [
                'name' => 'Text Block',
                'description' => 'Rich text content with formatting',
                'icon' => 'heroicon-o-document-text',
                'category' => 'content',
            ],
            self::TYPE_IMAGE => [
                'name' => 'Image Block',
                'description' => 'Single image with caption',
                'icon' => 'heroicon-o-photo',
                'category' => 'media',
            ],
            self::TYPE_VIDEO => [
                'name' => 'Video Block',
                'description' => 'Embedded video content',
                'icon' => 'heroicon-o-video-camera',
                'category' => 'media',
            ],
            self::TYPE_GALLERY => [
                'name' => 'Image Gallery',
                'description' => 'Multiple images in a gallery layout',
                'icon' => 'heroicon-o-photo',
                'category' => 'media',
            ],
            self::TYPE_QUOTE => [
                'name' => 'Quote Block',
                'description' => 'Highlighted quote or testimonial',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'category' => 'content',
            ],
            self::TYPE_CODE => [
                'name' => 'Code Block',
                'description' => 'Syntax highlighted code',
                'icon' => 'heroicon-o-code-bracket',
                'category' => 'content',
            ],
            self::TYPE_BUTTON => [
                'name' => 'Button Block',
                'description' => 'Call-to-action button',
                'icon' => 'heroicon-o-cursor-arrow-rays',
                'category' => 'interactive',
            ],
            self::TYPE_SEPARATOR => [
                'name' => 'Separator',
                'description' => 'Visual separator or divider',
                'icon' => 'heroicon-o-minus',
                'category' => 'layout',
            ],
            self::TYPE_COLUMNS => [
                'name' => 'Columns Layout',
                'description' => 'Multi-column content layout',
                'icon' => 'heroicon-o-view-columns',
                'category' => 'layout',
            ],
            self::TYPE_ACCORDION => [
                'name' => 'Accordion',
                'description' => 'Collapsible content sections',
                'icon' => 'heroicon-o-bars-3-bottom-left',
                'category' => 'interactive',
            ],
            self::TYPE_TABS => [
                'name' => 'Tabs',
                'description' => 'Tabbed content interface',
                'icon' => 'heroicon-o-folder',
                'category' => 'interactive',
            ],
            self::TYPE_SLIDER => [
                'name' => 'Content Slider',
                'description' => 'Sliding content carousel',
                'icon' => 'heroicon-o-arrow-right-circle',
                'category' => 'interactive',
            ],
            self::TYPE_FORM => [
                'name' => 'Form Block',
                'description' => 'Contact or custom form',
                'icon' => 'heroicon-o-clipboard-document-list',
                'category' => 'interactive',
            ],
            self::TYPE_MAP => [
                'name' => 'Map Block',
                'description' => 'Embedded map location',
                'icon' => 'heroicon-o-map-pin',
                'category' => 'interactive',
            ],
            self::TYPE_SOCIAL => [
                'name' => 'Social Media',
                'description' => 'Social media embed or sharing',
                'icon' => 'heroicon-o-share',
                'category' => 'interactive',
            ],
            self::TYPE_CUSTOM => [
                'name' => 'Custom HTML',
                'description' => 'Custom HTML content',
                'icon' => 'heroicon-o-code-bracket-square',
                'category' => 'advanced',
            ],
        ];
    }

    public static function getBlocksByCategory($category)
    {
        return static::where('category', $category)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function duplicate()
    {
        $duplicate = $this->replicate();
        $duplicate->name = $this->name . ' (Copy)';
        $duplicate->save();

        return $duplicate;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}