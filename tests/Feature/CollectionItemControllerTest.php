<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_item_page_returns_successful_response(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create([
            'slug' => 'blog',
        ]);
        CollectionItem::factory()->create([
            'collection_id' => $collection->id,
            'user_id'       => $user->id,
            'slug'          => 'my-first-post',
            'status'        => 'published',
        ]);

        $response = $this->get('/blog/my-first-post');

        $response->assertStatus(200);
    }

    public function test_collection_item_returns_404_for_wrong_collection(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['slug' => 'blog']);
        $otherCollection = Collection::factory()->create(['slug' => 'portfolio']);
        CollectionItem::factory()->create([
            'collection_id' => $collection->id,
            'user_id'       => $user->id,
            'slug'          => 'my-post',
        ]);

        $response = $this->get('/portfolio/my-post');

        $response->assertStatus(404);
    }

    public function test_collection_item_returns_404_for_nonexistent_item(): void
    {
        Collection::factory()->create(['slug' => 'blog']);

        $response = $this->get('/blog/nonexistent-item');

        $response->assertStatus(404);
    }

    public function test_collection_item_view_receives_correct_variables(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['slug' => 'blog']);
        $item = CollectionItem::factory()->create([
            'collection_id' => $collection->id,
            'user_id'       => $user->id,
            'slug'          => 'test-post',
            'title'         => 'Test Post Title',
        ]);

        $response = $this->get('/blog/test-post');

        $response->assertStatus(200);
        $response->assertViewIs('item');
        $response->assertViewHas('collection');
        $response->assertViewHas('item');
    }
}
