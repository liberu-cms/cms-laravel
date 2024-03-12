&lt;?php

namespace Tests\Unit\Providers;

use Tests\TestCase;
use App\Models\Page;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class AppServiceProviderTest extends TestCase
{
    public function test_auto_generation_of_slug_when_empty()
    {
        Event::fake();

        $title = "Test Page Title";
        $expectedSlug = Str::slug($title);

        $page = new Page([
            'title' => $title,
            'content' => 'This is a test page content',
            'slug' => '',
            'published_at' => now(),
            'user_id' => 1,
            'category_id' => 1,
        ]);

        $page->save();

        $this->assertNotEmpty($page->slug);
        $this->assertEquals($expectedSlug, $page->slug);

        Event::assertDispatched('eloquent.creating: App\Models\Page');
    }
}
