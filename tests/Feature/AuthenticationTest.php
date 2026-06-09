<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can view the login page', function (): void {
    $this->get('/login')->assertSuccessful();
});

it('can login with valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => 'user@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

it('cannot login with wrong password', function (): void {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('correct-password'),
    ]);

    $response = $this->post('/login', [
        'email' => 'user@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('cannot login with non-existent email', function (): void {
    $response = $this->post('/login', [
        'email' => 'nobody@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('requires email to login', function (): void {
    $response = $this->post('/login', [
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('requires password to login', function (): void {
    $response = $this->post('/login', [
        'email' => 'user@example.com',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

it('can logout when authenticated', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

it('unauthenticated users are redirected to login when accessing protected routes', function (): void {
    $this->get('/dashboard')->assertRedirect('/login');
});
