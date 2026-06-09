<?php

use App\Models\Membership;

it('uses the team_user table', function (): void {
    $membership = new Membership;

    expect($membership->getTable())->toBe('team_user');
});

it('has incrementing primary key', function (): void {
    $membership = new Membership;

    expect($membership->incrementing)->toBeTrue();
});
