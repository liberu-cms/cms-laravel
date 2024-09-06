<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $primaryKey = 'author_id';

    protected $fillable = [
        'author_name',
        'author_last_name',
        'author_email',
        'author_phone',
    ];

    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
