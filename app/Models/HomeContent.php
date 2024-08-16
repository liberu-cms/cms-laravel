<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_name',
        'content',
        'sort_order',
        'status'
    ];

    public function scopeActive(){
        return $this->where('status', 'active');
    }
}
