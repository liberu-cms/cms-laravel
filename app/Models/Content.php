<?php

namespace App\Models;

use App\Services\FileService;
use App\Traits\IsTenantModel;
use App\Traits\SEOable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Content extends Model
{
    use HasFactory, SEOable;
    use IsTenantModel;

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'body',
        'author_id',
        'published_at',
        'type',
        'category_id',
        'status',
        'featured_image_url',
        'slug',
        'is_draft',
        'last_autosaved_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'last_autosaved_at' => 'datetime',
        'is_draft' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($content) {
            if ($content->featured_image_url) {
                // Additional validation or processing logic
                $fileService = app(FileService::class);
                if (!$fileService->validateFileType($content->featured_image_url, 'image')) {
                    throw new \Exception('Invalid file type or size for featured image.');
                }
            }
        });

        static::saved(function ($content) {
            Cache::forget("content_{$content->id}");
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
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

}