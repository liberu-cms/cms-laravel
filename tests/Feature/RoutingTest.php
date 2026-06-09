<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves slug to a page when page exists', function (): void {
    $user = User::factory()->create();
    Page::factory()->create(['slug' => 'about', 'template' => 'default', 'user_id' => $user->id]);

    $this->get('/about')
        ->assertSuccessful()
        ->assertViewIs('templates.default');
});

it('resolves slug to a collection when no page but collection exists', function (): void {
    Collection::factory()->create(['slug' => 'news']);

    $this->get('/news')
        ->assertSuccessful()
        ->assertViewIs('collection');
});

it('page takes priority over collection when both share a slug', function (): void {
    $user = User::factory()->create();
    Page::factory()->create(['slug' => 'shared', 'template' => 'default', 'user_id' => $user->id]);
    Collection::factory()->create(['slug' => 'shared']);

    $this->get('/shared')
        ->assertSuccessful()
        ->assertViewIs('templates.default');
});

it('returns 404 when neither page nor collection matches the slug', function (): void {
    $this->get('/nonexistent-slug')->assertNotFound();
});

it('serves the home page at the root url', function (): void {
    $user = User::factory()->create();
    Page::factory()->home()->create(['user_id' => $user->id]);

    $this->get('/')->assertSuccessful()->assertViewIs('templates.home');
});

it('returns 404 for root url when home page does not exist', function (): void {
    $this->get('/')->assertNotFound();
});

it('resolves collection item via collection and item slugs', function (): void {
    $user = User::factory()->create();
    $collection = Collection::factory()->create(['slug' => 'blog']);
    CollectionItem::factory()->create([
        'collection_id' => $collection->id,
        'user_id' => $user->id,
        'slug' => 'my-post',
    ]);

    $this->get('/blog/my-post')->assertSuccessful()->assertViewIs('item');
});

it('returns 404 for collection item with wrong collection slug', function (): void {
    $user = User::factory()->create();
    $collection = Collection::factory()->create(['slug' => 'blog']);
    Collection::factory()->create(['slug' => 'portfolio']);
    CollectionItem::factory()->create([
        'collection_id' => $collection->id,
        'user_id' => $user->id,
        'slug' => 'my-post',
    ]);

    $this->get('/portfolio/my-post')->assertNotFound();
});

it('uses the named pages.show route for single slug segments', function (): void {
    expect(route('pages.show', 'about'))->toEndWith('/about');
});
