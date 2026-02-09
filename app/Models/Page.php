<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Biostate\FilamentMenuBuilder\Traits\Menuable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use IsTenantModel;
    use Menuable;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'published_at',
        'status',
        'user_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function getMenuLinkAttribute(): string
    {
        return route('pages.show', $this->slug);
    }

    public function getMenuNameAttribute(): string
    {
        return $this->title;
    }

    public static function getFilamentSearchLabel(): string
    {
        return 'title';
    }
}
