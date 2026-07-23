<?php

use App\Models\Collection;
use Biostate\FilamentMenuBuilder\DTO\Menu;
use Biostate\FilamentMenuBuilder\DTO\MenuItem;
use Liberu\Cms\Pages\Models\Page;

return [
    'models' => [
        // 'Product' => 'App\\Models\\Product',
        'Page' => Page::class,
        'Collection' => Collection::class,
    ],
    'api_enabled' => true,
    'cache' => [
        'enabled' => true,
        'key' => 'filament-menu-builder',
        'ttl' => 60 * 60 * 24,
    ],
    'usable_parameters' => [
        // For example:
        // 'mega_menu',
        // 'mega_menu_columns',
    ],
    'exclude_route_names' => [
        '/^debugbar\./', // Exclude debugbar routes
        '/^filament\./',   // Exclude filament routes
        '/^livewire\./',   // Exclude livewire routes
    ],
    'exclude_routes' => [
        //
    ],
    'dto' => [
        'menu' => Menu::class,
        'menu_item' => MenuItem::class,
    ],
];
