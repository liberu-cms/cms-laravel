<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

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
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function category()
    {
        return $this->belongsTo(ContentCategory::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tag()
    {
        return $this->belongsToMany(Tag::class);
    }
}
