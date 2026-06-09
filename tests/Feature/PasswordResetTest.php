<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

it('can view the forgot password page', function (): void {
    $this->get('/forgot-password')->assertSuccessful();
});

it('sends password reset notification to existing user', function (): void {
    Notification::fake();

    $user = User::factory()->create(['email' => 'user@example.com']);

    $this->post('/forgot-password', ['email' => 'user@example.com'])
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('reports an error when requesting reset link for unknown email', function (): void {
    Notification::fake();

    $this->post('/forgot-password', ['email' => 'nobody@example.com'])
        ->assertSessionHasErrors('email');

    Notification::assertNothingSent();
});

it('requires email field for password reset request', function (): void {
    $this->post('/forgot-password', [])
        ->assertSessionHasErrors('email');
});

it('requires valid email format for password reset request', function (): void {
    $this->post('/forgot-password', ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');
});

it('can view the reset password form with a valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $this->get("/reset-password/{$token}?email={$user->email}")
        ->assertSuccessful();
});

it('can reset password with a valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertSessionHas('status');
    expect(Hash::check('newpassword', $user->fresh()->password))->toBeTrue();
});

it('fails reset with invalid token', function (): void {
    $user = User::factory()->create();

    $response = $this->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $response->assertSessionHasErrors('email');
});

it('fails reset when passwords do not match', function (): void {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $response = $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'newpassword',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors('password');
});
