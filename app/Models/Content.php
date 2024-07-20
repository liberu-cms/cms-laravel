<?php

namespace App\Models;

use App\Traits\SEOable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function versions()
    {
        return $this->hasMany(ContentVersion::class)->orderBy('version_number', 'desc');
    }

    public function createVersion()
    {
        $latestVersion = $this->versions()->first();
        $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;

        return $this->versions()->create([
            'title' => $this->title,
            'body' => $this->body,
            'author_id' => $this->author_id,
            'published_at' => $this->published_at,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'featured_image_url' => $this->featured_image_url,
            'version_number' => $newVersionNumber,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'canonical_url' => $this->canonical_url,
            'slug' => $this->slug,
        ]);
    }

    public function rollbackToVersion(ContentVersion $version)
    {
        $this->update([
            'title' => $version->title,
            'body' => $version->body,
            'author_id' => $version->author_id,
            'published_at' => $version->published_at,
            'type' => $version->type,
            'category_id' => $version->category_id,
            'status' => $version->status,
            'featured_image_url' => $version->featured_image_url,
            'meta_title' => $version->meta_title,
            'meta_description' => $version->meta_description,
            'canonical_url' => $version->canonical_url,
            'slug' => $version->slug,
        ]);

        $this->createVersion();
    }
}