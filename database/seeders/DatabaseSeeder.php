<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();

        $this->call([
            RolesSeeder::class,
            TeamSeeder::class,
        ]);
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');
        $this->createTeamForUser($adminUser);

        $this->call([
            MenuSeeder::class,
            SiteSettingsSeeder::class,
        ]);

        
    }

    private function createTeamForUser($user)
    {
        $team = Team::first();
        $team->users()->attach($user);
        $user->current_team_id = 1;
        $user->save();
    }
}
