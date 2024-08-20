<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestLayoutManagment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'fk_menu_id',
        'sort_order',
        'is_active',
    ];

    public function scopeActive()
    {
        return $this->where('is_active', true);
    }
}
