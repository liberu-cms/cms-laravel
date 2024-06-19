<?php

namespace App\Models;

// use App\Traits\CreatedBy;
// use LaravelLiberu\Companies\Models\Company as CoreCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    // use CreatedBy;

    protected $fillable = [
        'privacy',
        'name',
        'email',
        'is_tenant',
        'status',
    ];
}
