<?php

namespace App\Models;

use App\Traits\IsTenantModel;
use Illuminate\Database\Eloquent\Model;
use Biostate\FilamentMenuBuilder\Models\Menu as BaseMenu;
use Biostate\FilamentMenuBuilder\Filament\Resources\MenuResource as BaseMenuResource;

class Menu extends BaseMenu
{
    use IsTenantModel;
}
