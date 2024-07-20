<?php

namespace App\Traits;

trait SEOable
{
    public function initializeSEOable()
    {
        $this->fillable = array_merge($this->fillable, [
            'meta_title',
            'meta_description',
            'canonical_url',
        ]);
    }

    public function getMetaTitleAttribute($value)
    {
        return $value ?: $this->title;
    }

    public function getMetaDescriptionAttribute($value)
    {
        return $value ?: substr(strip_tags($this->content), 0, 160);
    }

    public function getCanonicalUrlAttribute($value)
    {
        return $value ?: url($this->slug);
    }
}