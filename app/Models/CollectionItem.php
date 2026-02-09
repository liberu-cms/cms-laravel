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

    protected $fillable = [
        "title",
        "slug",
        "collection_id",
        "content",
        "status",
        "user_id",
        "published_at",
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
