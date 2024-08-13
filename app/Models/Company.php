<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    protected $fillable = [
        'privacy',
        'name',
        'email',
        'is_tenant',
        'status',
    ];
}
