<?php

/**
 * Livewire component for rendering the homepage of the application.
 * This includes displaying featured posts.
 */

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class Homepage extends Component
{
    public $featuredPosts;

    public function mount()
    {
        $this->featuredPosts = Post::where('featured', true)->get();
    }

    public function render()
    {
        return view('livewire.homepage', ['featuredPosts' => $this->featuredPosts]);
    }
}
