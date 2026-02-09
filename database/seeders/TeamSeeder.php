<?php

namespace Database\Seeders;

use Filament\Jetstream\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Team::firstOrCreate([
            'name' => 'Default',
            'personal_team' => true,
        ]);
    }
}
