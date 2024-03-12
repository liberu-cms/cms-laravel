<?php

/**
 * Page Model.
 *
 * Represents the Page entity in the database with relationships to User and Category models.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'slug',
        'published_at',
        'user_id',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
