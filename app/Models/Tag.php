<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    use IsTenantModel;

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function contents()
    {
        return $this->morphedByMany(Content::class, 'taggable');
    }

    public function pages()
    {
        return $this->morphedByMany(Page::class, 'taggable');
    }
}
