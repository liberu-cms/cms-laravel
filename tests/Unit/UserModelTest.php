<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('has correct fillable attributes', function (): void {
    $user = new User;

    expect($user->getFillable())
        ->toContain('name')
        ->toContain('email')
        ->toContain('password');
});

it('has correct hidden attributes', function (): void {
    $user = new User;

    expect($user->getHidden())
        ->toContain('password')
        ->toContain('remember_token')
        ->toContain('two_factor_recovery_codes')
        ->toContain('two_factor_secret');
});

it('appends profile_photo_url', function (): void {
    $user = new User;

    expect($user->getAppends())->toContain('profile_photo_url');
});

it('casts email_verified_at to datetime', function (): void {
    $user = User::factory()->create(['email_verified_at' => '2025-01-01 00:00:00']);

    expect($user->fresh()->email_verified_at)->toBeInstanceOf(Carbon::class);
});

it('returns profile_photo_path directly when it is a url', function (): void {
    $user = User::factory()->create([
        'profile_photo_path' => 'https://example.com/avatar.jpg',
    ]);

    expect($user->profile_photo_url)->toBe('https://example.com/avatar.jpg');
});

it('returns a fallback url when profile_photo_path is null', function (): void {
    $user = User::factory()->create(['profile_photo_path' => null]);

    expect($user->profile_photo_url)->toBeString()->not->toBeEmpty();
});

it('can be created with factory', function (): void {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
    $this->assertDatabaseHas('users', ['email' => $user->email]);
});

it('unverified factory state creates user with null email_verified_at', function (): void {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});

it('password is not included in serialized output', function (): void {
    $user = User::factory()->create();
    $array = $user->toArray();

    expect($array)->not->toHaveKey('password');
});

it('remember_token is not included in serialized output', function (): void {
    $user = User::factory()->create();
    $array = $user->toArray();

    expect($array)->not->toHaveKey('remember_token');
});
