<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $primaryKey = 'comment_id';

    protected $fillable = [
        'content_id',
        'author_id',
        'comment_body',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    
}
