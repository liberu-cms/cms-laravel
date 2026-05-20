<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'        => fake()->sentence(),
            'slug'         => fake()->unique()->slug(),
            'content'      => fake()->paragraphs(3, true),
            'template'     => 'default',
            'status'       => 'published',
            'published_at' => now(),
            'user_id'      => User::factory(),
        ];
    }

    public function home(): static
    {
        return $this->state(fn (array $attributes) => [
            'title'    => 'Home',
            'slug'     => 'home',
            'template' => 'home',
        ]);
    }
}
