<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects unauthenticated users to login', function (): void {
    $this->get('/dashboard')->assertRedirect('/login');
});
