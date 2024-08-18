<?php

/**
 * Livewire component for rendering the homepage of the application.
 * This includes displaying featured posts.
 */

namespace App\Http\Livewire;

use App\Models\HomeContent;
use Livewire\Component;
use App\Models\Post;

class Homepage extends Component
{
    protected $featuredPosts;
    protected $contents;

    public function mount()
    {
        $this->contents = HomeContent::active()->orderBy('sort_order')->get();
        $this->featuredPosts = Post::where('featured', true)->get();
    }

    public function render()
    {
        return view('livewire.homepage', [
            'featuredPosts' => $this->featuredPosts,
            'contents' => $this->contents
        ]);
    }
}
