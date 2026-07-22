<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Liberu\Cms\Hello\Events\HelloGreeted;

uses(RefreshDatabase::class);

it('serves a greeting, records it, and announces it on the bus', function (): void {
    Event::fake([HelloGreeted::class]);

    $response = $this->getJson('/api/v1/hello/ada');

    $response->assertSuccessful()
        ->assertJson(['module' => 'hello', 'message' => 'Hello, ada!']);

    $this->assertDatabaseHas('hello_greetings', ['name' => 'ada', 'message' => 'Hello, ada!']);

    Event::assertDispatched(HelloGreeted::class, fn (HelloGreeted $event): bool => $event->name === 'ada');
});

it('defaults the greeted name to world', function (): void {
    Event::fake([HelloGreeted::class]);

    $this->getJson('/api/v1/hello')
        ->assertSuccessful()
        ->assertJson(['message' => 'Hello, world!']);
});

it('loads the module migration while the module is enabled', function (): void {
    expect(Schema::hasTable('hello_greetings'))->toBeTrue();
});
