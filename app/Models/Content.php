<?php 

namespace App\Models;

use App\Services\FileService;
use App\Traits\SEOable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Content extends Model
{
    use HasFactory, SEOable;

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
    ];

    protected $casts = [
        'published_at' => 'datetime',
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

    public static function findCached($id)
    {
        return Cache::remember("content_{$id}", now()->addHours(24), function () use ($id) {
            return static::find($id);
        });
    }

}