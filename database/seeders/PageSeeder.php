<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Liberu\Cms\Pages\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Home',
                'slug' => 'home',
                'content' => '',
                'template' => 'home',
                'status' => 'published',
                'user_id' => User::first()->id,
            ],
            [
                'title' => 'About',
                'slug' => 'about',
                'content' => '',
                'template' => 'default',
                'status' => 'published',
                'user_id' => User::first()->id,
            ],
        ];

        foreach ($pages as $page) {
            if (config('app.multitenancy')) {
                $page['team_id'] = Team::first()->id;
            }

            Page::create($page);
        }

    }
}
