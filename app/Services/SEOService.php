<?php

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SEOService
{
    public function generateSitemap()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Add homepage
        $sitemap .= $this->addSitemapUrl(url('/'), now(), 'daily', '1.0');

        // Add published content
        $contents = Content::where('status', 'published')
            ->where('published_at', '<=', now())
            ->get();

        foreach ($contents as $content) {
            $url = url("/content/{$content->slug}");
            $lastmod = $content->updated_at;
            $changefreq = $this->getChangeFrequency($content);
            $priority = $this->getPriority($content);

            $sitemap .= $this->addSitemapUrl($url, $lastmod, $changefreq, $priority);
        }

        $sitemap .= '</urlset>';

        // Save sitemap
        File::put(public_path('sitemap.xml'), $sitemap);

        return $sitemap;
    }

    protected function addSitemapUrl($url, $lastmod, $changefreq = 'weekly', $priority = '0.5')
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
        $xml .= "    <lastmod>" . $lastmod->format('Y-m-d\TH:i:s\Z') . "</lastmod>\n";
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";

        return $xml;
    }

    protected function getChangeFrequency($content)
    {
        // Determine change frequency based on content type or update pattern
        switch ($content->type) {
            case 'news':
                return 'daily';
            case 'blog':
                return 'weekly';
            case 'page':
                return 'monthly';
            default:
                return 'weekly';
        }
    }

    protected function getPriority($content)
    {
        // Determine priority based on content importance
        if ($content->featured_image_url) {
            return '0.8';
        }

        switch ($content->type) {
            case 'page':
                return '0.9';
            case 'blog':
                return '0.7';
            default:
                return '0.5';
        }
    }

    public function generateMetaTags($content)
    {
        $meta = [];

        // Basic meta tags
        $meta['title'] = $content->seo_title ?: $content->title;
        $meta['description'] = $content->seo_description ?: Str::limit(strip_tags($content->body), 160);
        $meta['keywords'] = $content->seo_keywords ?: $this->extractKeywords($content->body);

        // Open Graph tags
        $meta['og:title'] = $meta['title'];
        $meta['og:description'] = $meta['description'];
        $meta['og:type'] = 'article';
        $meta['og:url'] = url("/content/{$content->slug}");

        if ($content->featured_image_url) {
            $meta['og:image'] = $content->featured_image_url;
        }

        // Twitter Card tags
        $meta['twitter:card'] = 'summary_large_image';
        $meta['twitter:title'] = $meta['title'];
        $meta['twitter:description'] = $meta['description'];

        if ($content->featured_image_url) {
            $meta['twitter:image'] = $content->featured_image_url;
        }

        // Article specific tags
        if ($content->published_at) {
            $meta['article:published_time'] = $content->published_at->toISOString();
        }

        if ($content->updated_at) {
            $meta['article:modified_time'] = $content->updated_at->toISOString();
        }

        if ($content->author) {
            $meta['article:author'] = $content->author->name;
        }

        return $meta;
    }

    public function generateStructuredData($content)
    {
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $content->title,
            'description' => Str::limit(strip_tags($content->body), 160),
            'url' => url("/content/{$content->slug}"),
            'datePublished' => $content->published_at?->toISOString(),
            'dateModified' => $content->updated_at->toISOString(),
        ];

        if ($content->author) {
            $structuredData['author'] = [
                '@type' => 'Person',
                'name' => $content->author->name,
            ];
        }

        if ($content->featured_image_url) {
            $structuredData['image'] = [
                '@type' => 'ImageObject',
                'url' => $content->featured_image_url,
            ];
        }

        // Add organization data
        $structuredData['publisher'] = [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ];

        return $structuredData;
    }

    public function analyzeContent($content)
    {
        $analysis = [
            'score' => 0,
            'issues' => [],
            'suggestions' => [],
        ];

        $score = 100;

        // Check title length
        $titleLength = strlen($content->title);
        if ($titleLength < 30) {
            $analysis['issues'][] = 'Title is too short (less than 30 characters)';
            $analysis['suggestions'][] = 'Consider making your title more descriptive';
            $score -= 10;
        } elseif ($titleLength > 60) {
            $analysis['issues'][] = 'Title is too long (more than 60 characters)';
            $analysis['suggestions'][] = 'Consider shortening your title for better display in search results';
            $score -= 5;
        }

        // Check meta description
        $metaDescription = $content->seo_description ?: strip_tags($content->body);
        $metaLength = strlen($metaDescription);
        if ($metaLength < 120) {
            $analysis['issues'][] = 'Meta description is too short';
            $analysis['suggestions'][] = 'Add a meta description of 120-160 characters';
            $score -= 15;
        } elseif ($metaLength > 160) {
            $analysis['issues'][] = 'Meta description is too long';
            $analysis['suggestions'][] = 'Shorten your meta description to under 160 characters';
            $score -= 10;
        }

        // Check for featured image
        if (!$content->featured_image_url) {
            $analysis['issues'][] = 'No featured image set';
            $analysis['suggestions'][] = 'Add a featured image to improve social media sharing';
            $score -= 10;
        }

        // Check content length
        $contentLength = strlen(strip_tags($content->body));
        if ($contentLength < 300) {
            $analysis['issues'][] = 'Content is too short';
            $analysis['suggestions'][] = 'Consider adding more content (aim for at least 300 words)';
            $score -= 20;
        }

        // Check for headings
        $headingCount = substr_count($content->body, '<h');
        if ($headingCount === 0) {
            $analysis['issues'][] = 'No headings found in content';
            $analysis['suggestions'][] = 'Add headings (H2, H3) to structure your content better';
            $score -= 10;
        }

        // Check for internal links
        $internalLinkCount = substr_count($content->body, url('/'));
        if ($internalLinkCount === 0) {
            $analysis['suggestions'][] = 'Consider adding internal links to related content';
            $score -= 5;
        }

        // Check for alt text in images
        if (preg_match_all('/<img[^>]+>/i', $content->body, $matches)) {
            foreach ($matches[0] as $img) {
                if (!preg_match('/alt\s*=\s*["\'][^"\']*["\']/', $img)) {
                    $analysis['issues'][] = 'Images missing alt text';
                    $analysis['suggestions'][] = 'Add descriptive alt text to all images';
                    $score -= 5;
                    break;
                }
            }
        }

        $analysis['score'] = max(0, $score);

        return $analysis;
    }

    public function extractKeywords($text, $limit = 10)
    {
        // Remove HTML tags and convert to lowercase
        $text = strtolower(strip_tags($text));

        // Remove common stop words
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did',
            'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those'
        ];

        // Extract words
        preg_match_all('/\b[a-z]{3,}\b/', $text, $matches);
        $words = $matches[0];

        // Filter out stop words
        $words = array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords);
        });

        // Count word frequency
        $wordCounts = array_count_values($words);
        arsort($wordCounts);

        // Return top keywords
        return implode(', ', array_slice(array_keys($wordCounts), 0, $limit));
    }

    public function generateRobotsTxt()
    {
        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "Disallow: /temp/\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";

        File::put(public_path('robots.txt'), $robots);

        return $robots;
    }

    public function getPageSpeed($url)
    {
        // This would integrate with Google PageSpeed Insights API
        // For now, return mock data
        return [
            'score' => 85,
            'metrics' => [
                'first_contentful_paint' => 1.2,
                'largest_contentful_paint' => 2.1,
                'cumulative_layout_shift' => 0.05,
            ],
            'suggestions' => [
                'Optimize images',
                'Minify CSS and JavaScript',
                'Enable compression',
            ]
        ];
    }

    public function trackKeywordRankings($keywords)
    {
        // This would integrate with SEO tracking APIs
        // For now, return mock data
        $rankings = [];

        foreach ($keywords as $keyword) {
            $rankings[$keyword] = [
                'position' => rand(1, 100),
                'url' => url('/'),
                'search_volume' => rand(100, 10000),
                'difficulty' => rand(1, 100),
            ];
        }

        return $rankings;
    }
}