<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Blockable extends Pivot
{
    protected $table = 'blockables';

    protected $fillable = [
        'content_block_id',
        'blockable_id',
        'blockable_type',
        'order',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'order' => 'integer',
    ];

    public function contentBlock()
    {
        return $this->belongsTo(ContentBlock::class);
    }

    public function blockable()
    {
        return $this->morphTo();
    }

    public function getSetting($key, $default = null)
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();

        return $this;
    }
}