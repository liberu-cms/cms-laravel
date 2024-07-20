<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class PostComponent extends Component
{
    public function render()
    {
        $featuredPosts = Post::where('featured', true)->take(3)->get();
        $latestPosts = Post::latest()->take(5)->get();
        return view('livewire.post-component', compact('featuredPosts', 'latestPosts'));
    }
}