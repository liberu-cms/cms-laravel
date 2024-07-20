<?php

namespace Tests\Unit\Models;

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_can_create_version()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create([
            'author_id' => $user->id,
        ]);

        $this->assertCount(0, $content->versions);

        $content->createVersion();

        $this->assertCount(1, $content->fresh()->versions);
    }

    public function test_content_can_rollback_to_version()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create([
            'author_id' => $user->id,
            'title' => 'Original Title',
            'body' => 'Original Body',
        ]);

        $content->createVersion();

        $content->update([
            'title' => 'Updated Title',
            'body' => 'Updated Body',
        ]);

        $content->createVersion();

        $this->assertEquals('Updated Title', $content->fresh()->title);
        $this->assertEquals('Updated Body', $content->fresh()->body);

        $oldVersion = $content->versions()->orderBy('version_number')->first();
        $content->rollbackToVersion($oldVersion);

        $this->assertEquals('Original Title', $content->fresh()->title);
        $this->assertEquals('Original Body', $content->fresh()->body);
        $this->assertCount(3, $content->fresh()->versions);
    }
}