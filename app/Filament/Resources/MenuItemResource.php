<?php

namespace App\Filament\Resources;

use Biostate\FilamentMenuBuilder\Filament\Resources\MenuItemResource as BaseMenuItemResource;

class MenuItemResource extends BaseMenuItemResource
{
    protected static bool $shouldRegisterNavigation = false;
}