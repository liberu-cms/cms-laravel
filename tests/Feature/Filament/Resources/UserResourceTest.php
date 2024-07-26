<?php

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_user_resource()
    {
        $this->actingAs(User::factory()->create());

        $this->get(UserResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('Users');
    }

    public function test_can_create_user()
    {
        $this->actingAs(User::factory()->create());

        $this->post(UserResource::getUrl('create'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    // Add more tests for editing, deleting, and assigning roles to users
}