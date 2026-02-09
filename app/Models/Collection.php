<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Biostate\FilamentMenuBuilder\Traits\Menuable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    use HasFactory;
    use IsTenantModel;
    use Menuable;

    protected $fillable = [
        "name",
        "slug",
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CollectionItem::class);
    }

    public function getMenuLinkAttribute(): string
    {
        return route('pages.show', $this->slug);
    }

    public function getMenuNameAttribute(): string
    {
        return $this->name;
    }

    public static function getFilamentSearchLabel(): string
    {
        return 'name';
    }
}
