<?php

namespace App\Filament\Resources;

use Biostate\FilamentMenuBuilder\Filament\Resources\MenuItemResource as BaseMenuItemResource;

class MenuItemResource extends BaseMenuItemResource
{
    #[\Override]
    protected static bool $shouldRegisterNavigation = false;
}
