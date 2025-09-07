<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShortcodeService
{
    protected $shortcodes = [];

    public function __construct()
    {
        $this->loadDefaultShortcodes();
        $this->loadPluginShortcodes();
    }

    public function register($tag, $callback)
    {
        $this->shortcodes[$tag] = $callback;
    }

    public function unregister($tag)
    {
        unset($this->shortcodes[$tag]);
    }

    public function render($content)
    {
        if (empty($this->shortcodes)) {
            return $content;
        }

        // Pattern to match shortcodes: [shortcode attr="value"]content[/shortcode]
        $pattern = '/\[(\w+)([^\]]*)\](?:(.*?)\[\/\1\])?/s';

        return preg_replace_callback($pattern, function ($matches) {
            $tag = $matches[1];
            $attributes = $this->parseAttributes($matches[2] ?? '');
            $content = $matches[3] ?? '';

            if (isset($this->shortcodes[$tag])) {
                return $this->executeShortcode($tag, $attributes, $content);
            }

            return $matches[0]; // Return original if shortcode not found
        }, $content);
    }

    protected function executeShortcode($tag, $attributes, $content)
    {
        try {
            $callback = $this->shortcodes[$tag];

            if (is_callable($callback)) {
                return call_user_func($callback, $attributes, $content);
            }

            if (is_string($callback) && class_exists($callback)) {
                $instance = new $callback();
                return $instance->render($attributes, $content);
            }

            return '';
        } catch (\Exception $e) {
            \Log::error("Shortcode error for [{$tag}]: " . $e->getMessage());
            return "<!-- Shortcode error: {$tag} -->";
        }
    }

    protected function parseAttributes($attributeString)
    {
        $attributes = [];

        if (empty(trim($attributeString))) {
            return $attributes;
        }

        // Pattern to match attributes: attr="value" or attr='value' or attr=value
        $pattern = '/(\w+)=(["\']?)([^"\'\s]*)\2/';

        preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $attributes[$match[1]] = $match[3];
        }

        return $attributes;
    }

    protected function loadDefaultShortcodes()
    {
        // Button shortcode
        $this->register('button', function ($attributes, $content) {
            $url = $attributes['url'] ?? '#';
            $style = $attributes['style'] ?? 'primary';
            $size = $attributes['size'] ?? 'medium';
            $target = isset($attributes['target']) ? 'target="' . $attributes['target'] . '"' : '';

            return "<a href=\"{$url}\" class=\"btn btn-{$style} btn-{$size}\" {$target}>{$content}</a>";
        });

        // Image shortcode
        $this->register('image', function ($attributes, $content) {
            $src = $attributes['src'] ?? '';
            $alt = $attributes['alt'] ?? '';
            $width = isset($attributes['width']) ? "width=\"{$attributes['width']}\"" : '';
            $height = isset($attributes['height']) ? "height=\"{$attributes['height']}\"" : '';
            $class = isset($attributes['class']) ? "class=\"{$attributes['class']}\"" : '';

            return "<img src=\"{$src}\" alt=\"{$alt}\" {$width} {$height} {$class}>";
        });

        // Video shortcode
        $this->register('video', function ($attributes, $content) {
            $src = $attributes['src'] ?? '';
            $width = $attributes['width'] ?? '100%';
            $height = $attributes['height'] ?? '315';
            $autoplay = isset($attributes['autoplay']) ? 'autoplay' : '';
            $controls = isset($attributes['controls']) ? 'controls' : 'controls';

            if (Str::contains($src, ['youtube.com', 'youtu.be'])) {
                $videoId = $this->extractYouTubeId($src);
                return "<iframe width=\"{$width}\" height=\"{$height}\" src=\"https://www.youtube.com/embed/{$videoId}\" frameborder=\"0\" allowfullscreen></iframe>";
            }

            if (Str::contains($src, 'vimeo.com')) {
                $videoId = $this->extractVimeoId($src);
                return "<iframe width=\"{$width}\" height=\"{$height}\" src=\"https://player.vimeo.com/video/{$videoId}\" frameborder=\"0\" allowfullscreen></iframe>";
            }

            return "<video width=\"{$width}\" height=\"{$height}\" {$controls} {$autoplay}><source src=\"{$src}\">Your browser does not support the video tag.</video>";
        });

        // Gallery shortcode
        $this->register('gallery', function ($attributes, $content) {
            $images = explode(',', $attributes['images'] ?? '');
            $columns = $attributes['columns'] ?? '3';

            $html = "<div class=\"gallery gallery-columns-{$columns}\">";

            foreach ($images as $image) {
                $image = trim($image);
                if (!empty($image)) {
                    $html .= "<div class=\"gallery-item\"><img src=\"{$image}\" alt=\"Gallery image\"></div>";
                }
            }

            $html .= "</div>";

            return $html;
        });

        // Quote shortcode
        $this->register('quote', function ($attributes, $content) {
            $author = $attributes['author'] ?? '';
            $cite = $attributes['cite'] ?? '';

            $html = "<blockquote class=\"shortcode-quote\">";
            $html .= "<p>{$content}</p>";

            if ($author) {
                $html .= "<footer>";
                $html .= "<cite>{$author}</cite>";
                if ($cite) {
                    $html .= " - <a href=\"{$cite}\" target=\"_blank\">Source</a>";
                }
                $html .= "</footer>";
            }

            $html .= "</blockquote>";

            return $html;
        });

        // Columns shortcode
        $this->register('columns', function ($attributes, $content) {
            $count = $attributes['count'] ?? '2';
            $gap = $attributes['gap'] ?? '20px';

            return "<div class=\"shortcode-columns\" style=\"display: grid; grid-template-columns: repeat({$count}, 1fr); gap: {$gap};\">{$content}</div>";
        });

        // Column shortcode (for use within columns)
        $this->register('column', function ($attributes, $content) {
            return "<div class=\"shortcode-column\">{$content}</div>";
        });

        // Accordion shortcode
        $this->register('accordion', function ($attributes, $content) {
            $id = $attributes['id'] ?? 'accordion-' . uniqid();

            return "<div class=\"accordion\" id=\"{$id}\">{$content}</div>";
        });

        // Accordion item shortcode
        $this->register('accordion-item', function ($attributes, $content) {
            $title = $attributes['title'] ?? 'Accordion Item';
            $id = 'item-' . uniqid();

            return "
                <div class=\"accordion-item\">
                    <h3 class=\"accordion-header\">
                        <button class=\"accordion-button\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#{$id}\">
                            {$title}
                        </button>
                    </h3>
                    <div id=\"{$id}\" class=\"accordion-collapse collapse\">
                        <div class=\"accordion-body\">{$content}</div>
                    </div>
                </div>
            ";
        });

        // Tabs shortcode
        $this->register('tabs', function ($attributes, $content) {
            $id = $attributes['id'] ?? 'tabs-' . uniqid();

            return "<div class=\"shortcode-tabs\" id=\"{$id}\">{$content}</div>";
        });

        // Tab shortcode
        $this->register('tab', function ($attributes, $content) {
            $title = $attributes['title'] ?? 'Tab';
            $active = isset($attributes['active']) ? 'active' : '';

            return "<div class=\"tab-pane {$active}\" data-title=\"{$title}\">{$content}</div>";
        });

        // Alert shortcode
        $this->register('alert', function ($attributes, $content) {
            $type = $attributes['type'] ?? 'info';
            $dismissible = isset($attributes['dismissible']) ? 'alert-dismissible' : '';

            $html = "<div class=\"alert alert-{$type} {$dismissible}\" role=\"alert\">";
            $html .= $content;

            if ($dismissible) {
                $html .= "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>";
            }

            $html .= "</div>";

            return $html;
        });

        // Recent posts shortcode
        $this->register('recent-posts', function ($attributes, $content) {
            $count = (int) ($attributes['count'] ?? 5);
            $type = $attributes['type'] ?? null;
            $category = $attributes['category'] ?? null;

            $query = \App\Models\Content::published()->orderBy('published_at', 'desc');

            if ($type) {
                $query->where('type', $type);
            }

            if ($category) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            }

            $posts = $query->limit($count)->get();

            $html = "<div class=\"recent-posts\">";
            foreach ($posts as $post) {
                $html .= "<div class=\"recent-post\">";
                $html .= "<h4><a href=\"/content/{$post->slug}\">{$post->title}</a></h4>";
                $html .= "<p>{$post->excerpt}</p>";
                $html .= "</div>";
            }
            $html .= "</div>";

            return $html;
        });
    }

    protected function loadPluginShortcodes()
    {
        // Load shortcodes from active plugins
        $pluginShortcodes = \App\Models\Plugin::getPluginShortcodes();

        foreach ($pluginShortcodes as $tag => $shortcodeData) {
            $this->register($tag, $shortcodeData['class']);
        }
    }

    protected function extractYouTubeId($url)
    {
        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/', $url, $matches);
        return $matches[1] ?? '';
    }

    protected function extractVimeoId($url)
    {
        preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
        return $matches[1] ?? '';
    }

    public function getRegisteredShortcodes()
    {
        return array_keys($this->shortcodes);
    }

    public function getShortcodeHelp()
    {
        return [
            'button' => [
                'description' => 'Creates a styled button',
                'usage' => '[button url="#" style="primary" size="medium" target="_blank"]Button Text[/button]',
                'attributes' => [
                    'url' => 'Button link URL',
                    'style' => 'Button style (primary, secondary, success, danger, warning, info)',
                    'size' => 'Button size (small, medium, large)',
                    'target' => 'Link target (_blank, _self)',
                ]
            ],
            'image' => [
                'description' => 'Displays an image',
                'usage' => '[image src="/path/to/image.jpg" alt="Image description" width="300" height="200"]',
                'attributes' => [
                    'src' => 'Image source URL',
                    'alt' => 'Alternative text',
                    'width' => 'Image width',
                    'height' => 'Image height',
                    'class' => 'CSS classes',
                ]
            ],
            'video' => [
                'description' => 'Embeds a video',
                'usage' => '[video src="https://youtube.com/watch?v=..." width="560" height="315"]',
                'attributes' => [
                    'src' => 'Video URL (YouTube, Vimeo, or direct)',
                    'width' => 'Video width',
                    'height' => 'Video height',
                    'autoplay' => 'Auto-play video',
                    'controls' => 'Show video controls',
                ]
            ],
            'gallery' => [
                'description' => 'Creates an image gallery',
                'usage' => '[gallery images="/img1.jpg,/img2.jpg,/img3.jpg" columns="3"]',
                'attributes' => [
                    'images' => 'Comma-separated list of image URLs',
                    'columns' => 'Number of columns',
                ]
            ],
            'quote' => [
                'description' => 'Creates a styled quote',
                'usage' => '[quote author="John Doe" cite="https://example.com"]Quote text here[/quote]',
                'attributes' => [
                    'author' => 'Quote author',
                    'cite' => 'Source URL',
                ]
            ],
            'recent-posts' => [
                'description' => 'Shows recent posts',
                'usage' => '[recent-posts count="5" type="blog" category="news"]',
                'attributes' => [
                    'count' => 'Number of posts to show',
                    'type' => 'Content type filter',
                    'category' => 'Category slug filter',
                ]
            ],
        ];
    }
}