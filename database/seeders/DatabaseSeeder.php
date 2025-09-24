<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            Tables\PermissionsSeeder::class,
            RolesSeeder::class,
            TeamSeeder::class,
//            UserSeeder::class,
            MenuSeeder::class,
            SiteSettingsSeeder::class,
            GuestLayoutManagmentSeeder::class,
        ]);
    }
}
