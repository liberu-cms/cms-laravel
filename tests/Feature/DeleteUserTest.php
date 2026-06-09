<?php

use App\Actions\Jetstream\DeleteUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes the user via the action', function (): void {
    $user = User::factory()->create();
    $userId = $user->id;

    app(DeleteUser::class)->delete($user);

    $this->assertDatabaseMissing('users', ['id' => $userId]);
});

it('deletes the user api tokens when deleting user', function (): void {
    $user = User::factory()->create();
    $user->createToken('test-token');

    $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id]);

    app(DeleteUser::class)->delete($user);

    $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
});
