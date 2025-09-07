<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ContentService
{
    public function getPopularContent($days = 30, $limit = 10)
    {
        return Content::published()
            ->popular($days)
            ->limit($limit)
            ->get();
    }

    public function getRecentContent($days = 7, $limit = 10)
    {
        return Content::published()
            ->recent($days)
            ->limit($limit)
            ->get();
    }

    public function getFeaturedContent($limit = 5)
    {
        return Content::published()
            ->featured()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getStickyContent()
    {
        return Content::published()
            ->sticky()
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function getContentByType($type, $limit = 10)
    {
        return Content::published()
            ->byType($type)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getContentByCategory($categoryId, $limit = 10)
    {
        return Content::published()
            ->byCategory($categoryId)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getContentByAuthor($authorId, $limit = 10)
    {
        return Content::published()
            ->byAuthor($authorId)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getContentWithTag($tag, $limit = 10)
    {
        return Content::published()
            ->withTag($tag)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function searchContent($query, $limit = 20)
    {
        return Content::published()
            ->search($query)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRelatedContent(Content $content, $limit = 5)
    {
        // First try to get manually related content
        $related = $content->relatedContent();

        if ($related->count() >= $limit) {
            return $related->take($limit);
        }

        // If not enough manual relations, find similar content
        $similarContent = Content::published()
            ->where('id', '!=', $content->id)
            ->where(function ($query) use ($content) {
                // Same category
                if ($content->category_id) {
                    $query->where('category_id', $content->category_id);
                }

                // Same type
                $query->orWhere('type', $content->type);

                // Similar tags
                if ($content->tags) {
                    foreach ($content->tags as $tag) {
                        $query->orWhereJsonContains('tags', $tag);
                    }
                }
            })
            ->orderBy('published_at', 'desc')
            ->limit($limit - $related->count())
            ->get();

        return $related->merge($similarContent)->take($limit);
    }

    public function getContentStats($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->subDays(30);
        $endDate = $endDate ?: now();

        return Cache::remember("content_stats_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}", 3600, function () use ($startDate, $endDate) {
            $totalContent = Content::count();
            $publishedContent = Content::where('workflow_status', 'published')->count();
            $draftContent = Content::where('workflow_status', 'draft')->count();
            $reviewContent = Content::where('workflow_status', 'review')->count();

            $recentContent = Content::whereBetween('created_at', [$startDate, $endDate])->count();
            $recentPublished = Content::where('workflow_status', 'published')
                ->whereBetween('published_at', [$startDate, $endDate])
                ->count();

            $topAuthors = Content::select('author_id', DB::raw('count(*) as content_count'))
                ->with('author')
                ->groupBy('author_id')
                ->orderBy('content_count', 'desc')
                ->limit(5)
                ->get();

            $contentByType = Content::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type');

            $contentByStatus = Content::select('workflow_status', DB::raw('count(*) as count'))
                ->groupBy('workflow_status')
                ->get()
                ->pluck('count', 'workflow_status');

            return [
                'total_content' => $totalContent,
                'published_content' => $publishedContent,
                'draft_content' => $draftContent,
                'review_content' => $reviewContent,
                'recent_content' => $recentContent,
                'recent_published' => $recentPublished,
                'top_authors' => $topAuthors,
                'content_by_type' => $contentByType,
                'content_by_status' => $contentByStatus,
                'publish_rate' => $totalContent > 0 ? round(($publishedContent / $totalContent) * 100, 2) : 0,
            ];
        });
    }

    public function getContentCalendar($month = null, $year = null)
    {
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Content::whereBetween('published_at', [$startDate, $endDate])
            ->orWhereBetween('scheduled_for', [$startDate, $endDate])
            ->orderBy('published_at')
            ->orderBy('scheduled_for')
            ->get()
            ->groupBy(function ($content) {
                return ($content->published_at ?: $content->scheduled_for)->format('Y-m-d');
            });
    }

    public function scheduleContent(Content $content, Carbon $scheduledDate)
    {
        $content->schedule($scheduledDate);

        // Here you could add job scheduling logic
        // dispatch(new PublishContentJob($content))->delay($scheduledDate);

        return $content;
    }

    public function bulkImportContent(array $contentData)
    {
        $imported = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($contentData as $data) {
                try {
                    $content = new Content();
                    $content->fill($data);

                    // Generate slug if not provided
                    if (!isset($data['slug'])) {
                        $content->slug = $content->generateUniqueSlug($data['title']);
                    }

                    $content->save();
                    $imported[] = $content;
                } catch (\Exception $e) {
                    $errors[] = [
                        'data' => $data,
                        'error' => $e->getMessage()
                    ];
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
            'success_count' => count($imported),
            'error_count' => count($errors)
        ];
    }

    public function exportContent($filters = [])
    {
        $query = Content::query();

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('workflow_status', $filters['status']);
        }

        if (isset($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->with(['author', 'category'])->get();
    }

    public function duplicateContent(Content $original, array $overrides = [])
    {
        $duplicate = $original->replicate();

        // Apply overrides
        foreach ($overrides as $key => $value) {
            $duplicate->$key = $value;
        }

        // Set default values for duplicated content
        $duplicate->title = $overrides['title'] ?? $original->title . ' (Copy)';
        $duplicate->slug = $duplicate->generateUniqueSlug($duplicate->title);
        $duplicate->workflow_status = 'draft';
        $duplicate->published_at = null;
        $duplicate->scheduled_for = null;
        $duplicate->is_featured = false;
        $duplicate->is_sticky = false;

        $duplicate->save();

        // Copy content blocks if they exist
        if ($original->contentBlocks()->exists()) {
            foreach ($original->contentBlocks as $block) {
                $duplicate->addBlock($block, $block->pivot->order, $block->pivot->settings);
            }
        }

        return $duplicate;
    }

    public function getContentPerformance(Content $content, $days = 30)
    {
        $startDate = now()->subDays($days);

        return [
            'views' => $content->getViewsCount($startDate),
            'unique_views' => $content->getUniqueViewsCount($startDate),
            'social_shares' => $content->getSocialSharesCount(),
            'comments_count' => $content->comments()->count(),
            'reading_time' => $content->reading_time,
            'word_count' => $content->word_count,
            'content_score' => $content->content_score,
            'readability_score' => $content->readability_score,
        ];
    }

    public function optimizeContent(Content $content)
    {
        $suggestions = [];

        // Check title length
        $titleLength = strlen($content->title);
        if ($titleLength < 30) {
            $suggestions[] = 'Consider making your title longer (30-60 characters recommended)';
        } elseif ($titleLength > 60) {
            $suggestions[] = 'Consider shortening your title (30-60 characters recommended)';
        }

        // Check excerpt
        if (!$content->excerpt) {
            $suggestions[] = 'Add a custom excerpt to improve SEO and social sharing';
        }

        // Check featured image
        if (!$content->featured_image_url) {
            $suggestions[] = 'Add a featured image to improve engagement and social sharing';
        }

        // Check SEO fields
        if (!$content->seo_title) {
            $suggestions[] = 'Add an SEO title for better search engine optimization';
        }

        if (!$content->seo_description) {
            $suggestions[] = 'Add a meta description for better search engine results';
        }

        // Check content length
        if ($content->word_count < 300) {
            $suggestions[] = 'Consider adding more content (300+ words recommended for SEO)';
        }

        // Check tags
        if (!$content->tags || count($content->tags) === 0) {
            $suggestions[] = 'Add tags to improve content discoverability';
        }

        // Check internal links
        $internalLinkCount = substr_count($content->body, url('/'));
        if ($internalLinkCount === 0) {
            $suggestions[] = 'Add internal links to related content to improve SEO';
        }

        return $suggestions;
    }

    public function getContentTrends($days = 30)
    {
        $endDate = now();
        $startDate = $endDate->copy()->subDays($days);

        return Cache::remember("content_trends_{$days}days", 1800, function () use ($startDate, $endDate, $days) {
            // Content creation trend
            $contentCreation = Content::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Publishing trend
            $publishingTrend = Content::selectRaw('DATE(published_at) as date, COUNT(*) as count')
                ->whereBetween('published_at', [$startDate, $endDate])
                ->where('workflow_status', 'published')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Popular tags
            $popularTags = Content::whereNotNull('tags')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get()
                ->pluck('tags')
                ->flatten()
                ->countBy()
                ->sortDesc()
                ->take(10);

            return [
                'content_creation' => $contentCreation,
                'publishing_trend' => $publishingTrend,
                'popular_tags' => $popularTags,
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'days' => $days
                ]
            ];
        });
    }
}