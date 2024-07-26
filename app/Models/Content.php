use Illuminate\Support\Facades\Cache;

class Content extends Model
{
    use HasFactory, SEOable;

    protected $primaryKey = 'content_id';

    protected $fillable = [
        'content_title',
        'content_body',
        'author_id',
        'published_date',
        'content_type',
        'category_id',
        'content_status',
        'featured_image_url',
        'language_code',
        'translation_group_id',
    ];

    protected $casts = [
        'published_date' => 'date',
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
            Cache::forget("content_{$content->content_id}");
        });
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }

    public function translations()
    {
        return $this->hasMany(Content::class, 'translation_group_id', 'translation_group_id')
            ->where('content_id', '!=', $this->content_id);
    }

    public static function findCached($id)
    {
        return Cache::remember("content_{$id}", now()->addHours(24), function () use ($id) {
            return static::find($id);
        });
    }

    public function getTranslation($languageCode)
    {
        return $this->translations()->where('language_code', $languageCode)->first();
    }
}