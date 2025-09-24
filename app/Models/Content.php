<?php

namespace App\Models;

use Str;
use DB;
use Hash;
use Exception;
use App\Services\FileService;
use App\Traits\IsTenantModel;
use App\Traits\SEOable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ContentStatusChanged;

class Content extends Model
{
    use HasFactory, SEOable;
    use IsTenantModel;

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'body',
        'excerpt',
        'author_id',
        'published_at',
        'type',
        'category_id',
        'status',
        'featured_image_url',
        'slug',
        'workflow_status',
        'scheduled_for',
        'review_by',
        'reviewed_at',
        'reviewed_by',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'reading_time',
        'word_count',
        'is_featured',
        'is_sticky',
        'allow_comments',
        'password_protected',
        'content_password',
        'template',
        'custom_fields',
        'tags',
        'related_content_ids',
        'social_shares',
        'last_modified_by',
        'content_score',
        'readability_score',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_sticky' => 'boolean',
        'allow_comments' => 'boolean',
        'password_protected' => 'boolean',
        'custom_fields' => 'array',
        'tags' => 'array',
        'related_content_ids' => 'array',
        'social_shares' => 'array',
        'reading_time' => 'integer',
        'word_count' => 'integer',
        'content_score' => 'float',
        'readability_score' => 'float',
    ];

    // Workflow status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_REVIEW = 'review';
    const STATUS_APPROVED = 'approved';
    const STATUS_PUBLISHED = 'published';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_REJECTED = 'rejected';

    public static function boot()
    {
        parent::boot();
        static::saving(function ($content) {
            if ($content->featured_image_url) {
                // Additional validation or processing logic
                $fileService = app(FileService::class);
                if (!$fileService->validateFileType($content->featured_image_url, 'image')) {
                    throw new Exception('Invalid file type or size for featured image.');
                }
            }

            // Set default workflow status if not set
            if (!$content->workflow_status) {
                $content->workflow_status = self::STATUS_DRAFT;
            }
        });

        static::saved(function ($content) {
            Cache::forget("content_{$content->id}");

            // Send notification if workflow status has changed
            if ($content->isDirty('workflow_status')) {
                $content->notifyStatusChange();
            }
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'review_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function category()
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->where('is_approved', true)->orderBy('created_at', 'desc');
    }

    public function allComments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function relatedContent()
    {
        if (!$this->related_content_ids) {
            return collect();
        }

        return static::whereIn('id', $this->related_content_ids)
            ->where('workflow_status', self::STATUS_PUBLISHED)
            ->get();
    }

    public function versions()
    {
        return $this->hasMany(ContentVersion::class)->orderBy('version_number', 'desc');
    }

    public function createVersion()
    {
        $latestVersion = $this->versions()->first();
        $versionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        return $this->versions()->create([
            'title' => $this->title,
            'body' => $this->body,
            'author_id' => Auth::id() ?? $this->author_id,
            'version_number' => $versionNumber,
            'published_at' => $this->published_at,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'featured_image_url' => $this->featured_image_url,
            'slug' => $this->slug,
            'workflow_status' => $this->workflow_status,
            'scheduled_for' => $this->scheduled_for,
            'review_by' => $this->review_by,
            'reviewed_at' => $this->reviewed_at,
            'reviewed_by' => $this->reviewed_by,
        ]);
    }

    public function rollbackToVersion(ContentVersion $version)
    {
        $this->update([
            'title' => $version->title,
            'body' => $version->body,
            'published_at' => $version->published_at,
            'type' => $version->type,
            'category_id' => $version->category_id,
            'status' => $version->status,
            'featured_image_url' => $version->featured_image_url,
            'slug' => $version->slug,
        ]);

        // Create a new version to record this rollback
        $this->createVersion();

        return $this;
    }

    public function getVersionDiff(ContentVersion $oldVersion, ContentVersion $newVersion = null)
    {
        if (!$newVersion) {
            // Compare with current version
            return [
                'title' => $this->diffText($oldVersion->title, $this->title),
                'body' => $this->diffHtml($oldVersion->body, $this->body),
                'published_at' => $oldVersion->published_at != $this->published_at,
                'type' => $oldVersion->type != $this->type,
                'category_id' => $oldVersion->category_id != $this->category_id,
                'status' => $oldVersion->status != $this->status,
                'featured_image_url' => $oldVersion->featured_image_url != $this->featured_image_url,
                'slug' => $oldVersion->slug != $this->slug,
            ];
        }

        return [
            'title' => $this->diffText($oldVersion->title, $newVersion->title),
            'body' => $this->diffHtml($oldVersion->body, $newVersion->body),
            'published_at' => $oldVersion->published_at != $newVersion->published_at,
            'type' => $oldVersion->type != $newVersion->type,
            'category_id' => $oldVersion->category_id != $newVersion->category_id,
            'status' => $oldVersion->status != $newVersion->status,
            'featured_image_url' => $oldVersion->featured_image_url != $newVersion->featured_image_url,
            'slug' => $oldVersion->slug != $newVersion->slug,
        ];
    }

    protected function diffText($old, $new)
    {
        if ($old === $new) {
            return ['type' => 'unchanged', 'content' => $old];
        }

        return ['type' => 'changed', 'old' => $old, 'new' => $new];
    }

    protected function diffHtml($old, $new)
    {
        if ($old === $new) {
            return ['type' => 'unchanged', 'content' => $old];
        }

        // For HTML content, we'll use a simple approach for now
        // In a real implementation, you might want to use a more sophisticated HTML diff library
        return ['type' => 'changed', 'old' => $old, 'new' => $new];
    }

    public static function findCached($id)
    {
        return Cache::remember("content_{$id}", now()->addHours(24), function () use ($id) {
            return static::find($id);
        });
    }

    public function isDraft()
    {
        return $this->workflow_status === self::STATUS_DRAFT;
    }

    public function isInReview()
    {
        return $this->workflow_status === self::STATUS_REVIEW;
    }

    public function isApproved()
    {
        return $this->workflow_status === self::STATUS_APPROVED;
    }

    public function isPublished()
    {
        return $this->workflow_status === self::STATUS_PUBLISHED;
    }

    public function isScheduled()
    {
        return $this->workflow_status === self::STATUS_SCHEDULED;
    }

    public function isRejected()
    {
        return $this->workflow_status === self::STATUS_REJECTED;
    }

    public function submitForReview($reviewerId = null)
    {
        $this->workflow_status = self::STATUS_REVIEW;
        $this->review_by = $reviewerId;
        $this->save();

        return $this;
    }

    public function approve()
    {
        $this->workflow_status = self::STATUS_APPROVED;
        $this->reviewed_at = now();
        $this->reviewed_by = Auth::id();
        $this->save();

        return $this;
    }

    public function reject()
    {
        $this->workflow_status = self::STATUS_REJECTED;
        $this->reviewed_at = now();
        $this->reviewed_by = Auth::id();
        $this->save();

        return $this;
    }

    public function publish()
    {
        $this->workflow_status = self::STATUS_PUBLISHED;
        $this->published_at = now();
        $this->save();

        return $this;
    }

    public function schedule($scheduledDate)
    {
        $this->workflow_status = self::STATUS_SCHEDULED;
        $this->scheduled_for = $scheduledDate;
        $this->save();

        return $this;
    }

    public function notifyStatusChange()
    {
        $author = $this->author;
        if ($author) {
            Notification::send($author, new ContentStatusChanged($this));
        }

        // Also notify reviewer if content is submitted for review
        if ($this->workflow_status === self::STATUS_REVIEW && $this->reviewer) {
            Notification::send($this->reviewer, new ContentStatusChanged($this));
        }
    }

    public function analytics()
    {
        return $this->hasMany(ContentAnalytics::class);
    }

    public function recordView($isUnique = false, $source = null, $deviceType = null, $country = null, $referrer = null)
    {
        return ContentAnalytics::recordView(
            $this->id,
            $isUnique,
            $source,
            $deviceType,
            $country,
            $referrer
        );
    }

    public function recordInteraction($type, $value = 1)
    {
        return ContentAnalytics::recordInteraction($this->id, $type, $value);
    }

    public function updateAnalyticsMetrics($avgTimeOnPage = null, $bounceRate = null, $conversionRate = null)
    {
        return ContentAnalytics::updateMetrics(
            $this->id,
            $avgTimeOnPage,
            $bounceRate,
            $conversionRate
        );
    }

    public function getViewsCount($startDate = null, $endDate = null)
    {
        $query = $this->analytics();

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->sum('views');
    }

    public function getUniqueViewsCount($startDate = null, $endDate = null)
    {
        $query = $this->analytics();

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }

        return $query->sum('unique_views');
    }



    public function reviewBy($userId)
    {
        $this->review_by = $userId;
        $this->save();
    }

    public function review()
    {
        $this->reviewed_at = now();
        $this->reviewed_by = Auth::id();
        $this->save();
    }

    public function contentBlocks()
    {
        return $this->morphToMany(ContentBlock::class, 'blockable')
            ->using(Blockable::class)
            ->withPivot('order', 'settings')
            ->orderBy('order');
    }

    public function addBlock(ContentBlock $block, $order = null, $settings = [])
    {
        if ($order === null) {
            $order = $this->contentBlocks()->count();
        }

        $this->contentBlocks()->attach($block->id, [
            'order' => $order,
            'settings' => $settings,
        ]);

        return $this;
    }

    public function removeBlock(ContentBlock $block)
    {
        $this->contentBlocks()->detach($block->id);

        // Reorder remaining blocks
        $this->reorderBlocks();

        return $this;
    }

    public function reorderBlocks()
    {
        $blocks = $this->contentBlocks()->orderBy('order')->get();

        foreach ($blocks as $index => $block) {
            $this->contentBlocks()->updateExistingPivot($block->id, [
                'order' => $index,
            ]);
        }

        return $this;
    }

    public function renderBlocks()
    {
        $html = '';

        foreach ($this->contentBlocks as $block) {
            $html .= $block->render();
        }

        return $html;
    }

    // Content generation and analysis methods
    public function generateExcerpt($length = 160)
    {
        $text = strip_tags($this->body);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }

    public function calculateWordCount()
    {
        $text = strip_tags($this->body);
        $text = preg_replace('/\s+/', ' ', $text);
        return str_word_count($text);
    }

    public function calculateReadingTime($wordsPerMinute = 200)
    {
        $wordCount = $this->calculateWordCount();
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    public function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function calculateContentScore()
    {
        $score = 0;
        $maxScore = 100;

        // Title length (10 points)
        $titleLength = strlen($this->title);
        if ($titleLength >= 30 && $titleLength <= 60) {
            $score += 10;
        } elseif ($titleLength >= 20 && $titleLength <= 80) {
            $score += 5;
        }

        // Content length (20 points)
        $wordCount = $this->word_count ?? $this->calculateWordCount();
        if ($wordCount >= 300) {
            $score += 20;
        } elseif ($wordCount >= 150) {
            $score += 10;
        }

        // SEO fields (20 points)
        if ($this->seo_title) $score += 5;
        if ($this->seo_description) $score += 10;
        if ($this->seo_keywords) $score += 5;

        // Featured image (10 points)
        if ($this->featured_image_url) $score += 10;

        // Excerpt (10 points)
        if ($this->excerpt) $score += 10;

        // Tags (10 points)
        if ($this->tags && count($this->tags) > 0) $score += 10;

        // Headings in content (10 points)
        $headingCount = substr_count($this->body, '<h');
        if ($headingCount >= 2) {
            $score += 10;
        } elseif ($headingCount >= 1) {
            $score += 5;
        }

        // Internal links (10 points)
        $internalLinkCount = substr_count($this->body, url('/'));
        if ($internalLinkCount >= 2) {
            $score += 10;
        } elseif ($internalLinkCount >= 1) {
            $score += 5;
        }

        return min($score, $maxScore);
    }

    public function calculateReadabilityScore()
    {
        $text = strip_tags($this->body);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = str_word_count($text);
        $syllables = $this->countSyllables($text);

        if (count($sentences) == 0 || $words == 0) {
            return 0;
        }

        // Flesch Reading Ease Score
        $avgSentenceLength = $words / count($sentences);
        $avgSyllablesPerWord = $syllables / $words;

        $score = 206.835 - (1.015 * $avgSentenceLength) - (84.6 * $avgSyllablesPerWord);

        return max(0, min(100, $score));
    }

    protected function countSyllables($text)
    {
        $words = str_word_count($text, 1);
        $syllableCount = 0;

        foreach ($words as $word) {
            $word = strtolower($word);
            $syllableCount += max(1, preg_match_all('/[aeiouy]+/', $word));
        }

        return $syllableCount;
    }

    // Content filtering and search methods
    public function scopePublished($query)
    {
        return $query->where('workflow_status', self::STATUS_PUBLISHED)
                    ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSticky($query)
    {
        return $query->where('is_sticky', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', "%{$searchTerm}%")
              ->orWhere('body', 'like', "%{$searchTerm}%")
              ->orWhere('excerpt', 'like', "%{$searchTerm}%")
              ->orWhereJsonContains('tags', $searchTerm);
        });
    }

    public function scopePopular($query, $days = 30)
    {
        return $query->withCount(['analytics as total_views' => function ($q) use ($days) {
            $q->where('date', '>=', now()->subDays($days))->select(DB::raw('sum(views)'));
        }])->orderBy('total_views', 'desc');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('published_at', '>=', now()->subDays($days))
                    ->orderBy('published_at', 'desc');
    }

    // Social sharing methods
    public function incrementSocialShare($platform)
    {
        $shares = $this->social_shares ?? [];
        $shares[$platform] = ($shares[$platform] ?? 0) + 1;
        $this->social_shares = $shares;
        $this->save();

        return $this;
    }

    public function getSocialSharesCount($platform = null)
    {
        if (!$this->social_shares) {
            return $platform ? 0 : 0;
        }

        if ($platform) {
            return $this->social_shares[$platform] ?? 0;
        }

        return array_sum($this->social_shares);
    }

    public function getShareUrl($platform)
    {
        $url = urlencode(url("/content/{$this->slug}"));
        $title = urlencode($this->title);
        $description = urlencode($this->excerpt ?? $this->generateExcerpt());

        switch ($platform) {
            case 'facebook':
                return "https://www.facebook.com/sharer/sharer.php?u={$url}";
            case 'twitter':
                return "https://twitter.com/intent/tweet?url={$url}&text={$title}";
            case 'linkedin':
                return "https://www.linkedin.com/sharing/share-offsite/?url={$url}";
            case 'pinterest':
                $image = urlencode($this->featured_image_url ?? '');
                return "https://pinterest.com/pin/create/button/?url={$url}&media={$image}&description={$description}";
            case 'whatsapp':
                return "https://wa.me/?text={$title}%20{$url}";
            default:
                return url("/content/{$this->slug}");
        }
    }

    // Content validation and security
    public function isPasswordProtected()
    {
        return $this->password_protected && !empty($this->content_password);
    }

    public function checkPassword($password)
    {
        return $this->isPasswordProtected() && 
               Hash::check($password, $this->content_password);
    }

    public function setPassword($password)
    {
        $this->password_protected = true;
        $this->content_password = Hash::make($password);
        $this->save();

        return $this;
    }

    public function removePassword()
    {
        $this->password_protected = false;
        $this->content_password = null;
        $this->save();

        return $this;
    }

    // Custom fields management
    public function getCustomField($key, $default = null)
    {
        $fields = $this->custom_fields ?? [];
        return $fields[$key] ?? $default;
    }

    public function setCustomField($key, $value)
    {
        $fields = $this->custom_fields ?? [];
        $fields[$key] = $value;
        $this->custom_fields = $fields;
        $this->save();

        return $this;
    }

    public function removeCustomField($key)
    {
        $fields = $this->custom_fields ?? [];
        unset($fields[$key]);
        $this->custom_fields = $fields;
        $this->save();

        return $this;
    }

    // Template and rendering
    public function getTemplate()
    {
        return $this->template ?? 'default';
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        $this->save();

        return $this;
    }

    public function render($template = null)
    {
        $template = $template ?? $this->getTemplate();

        return view("content.templates.{$template}", [
            'content' => $this,
            'blocks' => $this->contentBlocks,
            'relatedContent' => $this->relatedContent(),
        ])->render();
    }

    // Performance and caching
    public function clearCache()
    {
        Cache::forget("content_{$this->id}");
        Cache::forget("content_slug_{$this->slug}");
        Cache::forget("content_analytics_{$this->id}");

        return $this;
    }

    public static function findBySlugCached($slug)
    {
        return Cache::remember("content_slug_{$slug}", now()->addHours(24), function () use ($slug) {
            return static::where('slug', $slug)
                         ->where('workflow_status', self::STATUS_PUBLISHED)
                         ->first();
        });
    }

    // Bulk operations
    public static function bulkUpdateStatus($ids, $status)
    {
        return static::whereIn('id', $ids)->update([
            'workflow_status' => $status,
            'last_modified_by' => Auth::id(),
        ]);
    }

    public static function bulkDelete($ids)
    {
        return static::whereIn('id', $ids)->delete();
    }

    public static function bulkFeature($ids, $featured = true)
    {
        return static::whereIn('id', $ids)->update([
            'is_featured' => $featured,
            'last_modified_by' => Auth::id(),
        ]);
    }
}
