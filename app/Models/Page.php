<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Biostate\FilamentMenuBuilder\Traits\Menuable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    use IsTenantModel;
    use Menuable;

    #[\Override]
    protected $fillable = [
        'title',
        'slug',
        'content',
        'published_at',
        'status',
        'user_id',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

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
