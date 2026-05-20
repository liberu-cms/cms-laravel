<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            ]
        ];

        foreach ($pages as $page) {
            if (env('MULTITENANCY', false)) 
                $page["team_id"] = Team::first()->id;
    
            Page::create($page);
        }
        
    }
}
