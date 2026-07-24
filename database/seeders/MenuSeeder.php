<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Liberu\Cms\Menus\Models\Menu;
use Liberu\Cms\Menus\Models\MenuItem;
use Liberu\Cms\Pages\Models\Page;

class MenuSeeder extends Seeder
{
    /**
     * Seed a primary navigation menu using the cms-menus module.
     */
    public function run(): void
    {
        $teamId = Team::first()?->id;

        $menu = Menu::create([
            'name' => 'Main',
            'location' => 'header',
            'team_id' => $teamId,
        ]);

        $items = [
            ['label' => 'Home', 'slug' => 'home', 'fallback' => '/'],
            ['label' => 'About', 'slug' => 'about', 'fallback' => '/about'],
            ['label' => 'Blog', 'slug' => null, 'fallback' => '/blog'],
        ];

        foreach ($items as $sort => $item) {
            $url = $item['slug'] !== null
                ? '/'.(Page::where('slug', $item['slug'])->value('slug') ?? ltrim($item['fallback'], '/'))
                : $item['fallback'];

            MenuItem::create([
                'menu_id' => $menu->id,
                'label' => $item['label'],
                'url' => $url,
                'sort' => $sort,
                'team_id' => $teamId,
            ]);
        }
    }
}
