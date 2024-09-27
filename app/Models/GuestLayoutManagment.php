<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $name = \Str::replace(' ', '_', $model->name);
            self::makeComponent($model->content, $name);
        });

        static::updating(function ($model) {
          $name = \Str::replace(' ', '_', $model->name);
           self::makeComponent($model->content, $name);
        });
    }

    public static function makeComponent($component, $name)
    {
        file_put_contents(
            resource_path('views/partials/elements/' . $name . '.blade.php'),
            $component
        );
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = \Str::replace(' ', '_', $value);
    }

    public function scopeActive()
    {
        return $this->where('is_active', true);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'fk_menu_id');
    }
}
