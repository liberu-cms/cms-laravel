<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            // SiteSettingsSeeder::class,
            // PermissionsTableSeeder::class,
            // RoleSeeder::class,
            CoreSetupSeeder::class,
            PageSeeder::class,
            CollectionSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            CollectionItemSeeder::class,
            MenuSeeder::class,
        ]);
    }
}
