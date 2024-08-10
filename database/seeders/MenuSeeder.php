<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $menus = [
            [
                'name' => 'Home',
                'url' => '/',
                'order' => 1
            ],
            [
                'name' => 'About',
                'url' => '/about',
                'order' => 2
            ],
            [
                'name' => 'Contact',
                'url' => '/contact',
                'order' => 3
            ],
        ];

        foreach ($menus as $menuData) {
            $this->createMenu($menuData);
        }
    }

    private function createMenu($menuData, $parentId = null)
    {
        $children = $menuData['children'] ?? [];
        unset($menuData['children']);

        $menuData['parent_id'] = $parentId;
        $menu = Menu::create($menuData);

        foreach ($children as $childData) {
            $this->createMenu($childData, $menu->id);
        }
    }
}