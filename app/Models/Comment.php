<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'parent_id',
        'author_name',
        'author_email',
        'author_url',
        'author_ip',
        'author_user_agent',
        'comment_content',
        'is_approved',
        'user_id',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->where('is_approved', true);
    }

    public function allReplies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function approve()
    {
        $this->is_approved = true;
        $this->save();
        return $this;
    }

    public function reject()
    {
        $this->is_approved = false;
        $this->save();
        return $this;
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }
}