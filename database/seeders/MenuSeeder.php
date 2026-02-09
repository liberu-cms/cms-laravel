<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            "main" => [
                [
                    'name' => 'Home',
                    "menuable_type" => Page::class,
                    "menuable_id" => Page::where("slug", "home")->first()?->id,
                    "type" => "model",
                ],
                [
                    'name' => 'About',
                    "menuable_type" => Page::class,
                    "menuable_id" => Page::where("slug", "about")->first()?->id,
                    "type" => "model",
                ],
                [
                    'name' => 'Blog',
                    "menuable_type" => Collection::class,
                    "menuable_id" => Collection::where("slug", "blog")->first()?->id,
                    "type" => "model",
                ],
                // [
                //     'name' => 'Contact',
                //     'url' => 'contact',
                //     'order' => 3
                // ],
            ]
        ];

        foreach ($menus as $menu => $menuItems) {
            $menu = Menu::create([
                "name" => $menu,
                "slug" => $menu,
                "team_id" => Team::first()?->id,
            ]);
            foreach ($menuItems as $menuData) {
                $this->createMenu($menu->id, $menuData);
            }
        }
    }

    private function createMenu($menuId, $menuData, $parentId = null)
    {
        $children = $menuData['children'] ?? [];
        unset($menuData['children']);

        $menuData['menu_id'] = $menuId;
        $menuData['parent_id'] = $parentId;
        $menuItem = MenuItem::create($menuData);

        foreach ($children as $childData) {
            $this->createMenu($menuId, $childData, $menuItem->id);
        }
    }
    
}
