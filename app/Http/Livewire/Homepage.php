&lt;?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class Homepage extends Component
{
    public $featuredPosts;

    public function mount()
    {
        $this->featuredPosts = Post::where('is_featured', true)->get();
    }

    public function render()
    {
        return view('livewire.homepage', ['featuredPosts' => $this->featuredPosts]);
    }
}
