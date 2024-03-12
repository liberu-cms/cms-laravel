&lt;?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $fillableAttributes = ['title', 'content', 'slug', 'published_at', 'user_id', 'category_id'];
        $pageModel = new Page();
        $this->assertEquals($fillableAttributes, $pageModel->getFillable());
    }

    public function test_category_relationship()
    {
        $pageModel = new Page();
        $this->assertInstanceOf(BelongsTo::class, $pageModel->category());
    }

    public function test_user_relationship()
    {
        $pageModel = new Page();
        $this->assertInstanceOf(BelongsTo::class, $pageModel->user());
    }
}
