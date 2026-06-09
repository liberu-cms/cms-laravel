<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('can update password with correct current password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $this->actingAs($user)
        ->put('/user/password', [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasNoErrors();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

it('cannot update password with wrong current password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $this->actingAs($user)
        ->put('/user/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password', errorBag: 'updatePassword');
});

it('requires current password to update password', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/user/password', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password', errorBag: 'updatePassword');
});

it('requires new password confirmation to match', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $this->actingAs($user)
        ->put('/user/password', [
            'current_password' => 'current-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])
        ->assertSessionHasErrors('password', errorBag: 'updatePassword');
});

it('requires new password to meet minimum length', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    $this->actingAs($user)
        ->put('/user/password', [
            'current_password' => 'current-password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors('password', errorBag: 'updatePassword');
});

it('unauthenticated users cannot update password', function (): void {
    $this->put('/user/password', [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect('/login');
});
