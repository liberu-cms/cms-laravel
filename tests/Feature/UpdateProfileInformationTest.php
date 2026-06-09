<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('authenticated user can view their profile page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/user/profile')
        ->assertSuccessful();
});

it('can update name', function (): void {
    $user = User::factory()->create(['name' => 'Old Name']);

    $this->actingAs($user)
        ->put('/user/profile-information', [
            'name' => 'New Name',
            'email' => $user->email,
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('New Name');
});

it('can update email', function (): void {
    $user = User::factory()->create(['email' => 'old@example.com']);

    $this->actingAs($user)
        ->put('/user/profile-information', [
            'name' => $user->name,
            'email' => 'new@example.com',
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->email)->toBe('new@example.com');
});

it('requires name when updating profile', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/user/profile-information', [
            'email' => $user->email,
        ])
        ->assertSessionHasErrors('name', errorBag: 'updateProfileInformation');
});

it('requires valid email when updating profile', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/user/profile-information', [
            'name' => $user->name,
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('email', errorBag: 'updateProfileInformation');
});

it('email must be unique excluding current user', function (): void {
    $user = User::factory()->create();
    User::factory()->create(['email' => 'taken@example.com']);

    $this->actingAs($user)
        ->put('/user/profile-information', [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ])
        ->assertSessionHasErrors('email', errorBag: 'updateProfileInformation');
});

it('can keep the same email when updating profile', function (): void {
    $user = User::factory()->create(['email' => 'same@example.com']);

    $this->actingAs($user)
        ->put('/user/profile-information', [
            'name' => 'Updated Name',
            'email' => 'same@example.com',
        ])
        ->assertSessionHasNoErrors();
});

it('unauthenticated users cannot update profile', function (): void {
    $this->put('/user/profile-information', [
        'name' => 'Test',
        'email' => 'test@example.com',
    ])->assertRedirect('/login');
});
