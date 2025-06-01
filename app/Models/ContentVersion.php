<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'title',
        'body',
        'author_id',
        'version_number',
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
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
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
}