<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionItem extends Model
{
    use HasFactory;
    use IsTenantModel;

    #[\Override]
    protected $fillable = [
        'title',
        'slug',
        'collection_id',
        'content',
        'status',
        'user_id',
        'published_at',
    ];

    #[\Override]
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
