<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use App\Traits\SEOable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory, SEOable;
    use IsTenantModel;

    protected $fillable = [
        'title',
        'content',
        'slug',
        'published_at',
        'user_id',
        'category_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
