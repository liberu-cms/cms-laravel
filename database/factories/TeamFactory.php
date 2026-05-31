<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    #[\Override]
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'user_id' => User::factory(),
            'personal_team' => true,
        ];
    }
}
