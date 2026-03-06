<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_page_returns_successful_response(): void
    {
        $user = User::factory()->create();
        Collection::factory()->create([
            'name' => 'Blog',
            'slug' => 'blog',
        ]);

        $response = $this->get('/blog');

        $response->assertStatus(200);
    }

    public function test_collection_page_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/nonexistent-collection');

        $response->assertStatus(404);
    }

    public function test_collection_view_receives_collection_variable(): void
    {
        Collection::factory()->create([
            'name' => 'Portfolio',
            'slug' => 'portfolio',
        ]);

        $response = $this->get('/portfolio');

        $response->assertStatus(200);
        $response->assertViewHas('collection');
        $response->assertViewHas('items');
    }

    public function test_collection_view_is_correct(): void
    {
        Collection::factory()->create([
            'slug' => 'news',
        ]);

        $response = $this->get('/news');

        $response->assertStatus(200);
        $response->assertViewIs('collection');
    }

    public function test_collection_items_are_paginated(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'slug' => 'articles',
        ]);

        CollectionItem::factory()->count(5)->create([
            'collection_id' => $collection->id,
            'user_id'       => $user->id,
            'status'        => 'published',
        ]);

        $response = $this->get('/articles');

        $response->assertStatus(200);
        $response->assertViewHas('items');
    }
}
