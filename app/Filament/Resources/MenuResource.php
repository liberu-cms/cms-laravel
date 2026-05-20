<?php

namespace App\Filament\Resources;

use Biostate\FilamentMenuBuilder\Filament\Resources\MenuResource as BaseMenuResource;

class MenuResource extends BaseMenuResource
{
    public static function getNavigationGroup(): ?string
    {
        return null;
    }
}