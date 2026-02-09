<?php 

namespace App\Traits;

use Filament\Jetstream\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait IsTenantModel 
{

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}