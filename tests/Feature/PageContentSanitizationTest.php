<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Cms\Pages\Models\Page;

uses(RefreshDatabase::class);

it('sanitizes page content rendered through the public controller', function (): void {
    Page::factory()->create([
        'slug' => 'sanitize-me',
        'template' => 'default',
        'content' => '<p>Visible safe text</p><script>alert("pwned")</script><p onclick="steal()">handler text</p>',
    ]);

    $response = $this->get('/sanitize-me');

    // The layout itself contains legitimate <script> tags (Vite/Livewire), so
    // assert the injected payloads specifically are stripped, not all scripts.
    $response->assertOk()
        ->assertSee('Visible safe text')
        ->assertSee('handler text')
        ->assertDontSee('alert("pwned")', false)
        ->assertDontSee('steal()', false)
        ->assertDontSee('onclick="steal', false);
});
