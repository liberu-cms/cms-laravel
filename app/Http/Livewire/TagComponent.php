<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Tag;

class TagComponent extends Component
{
    public function render()
    {
        $tags = Tag::withCount('contents')->orderByDesc('contents_count')->take(10)->get();
        return view('livewire.tag-component', compact('tags'));
    }
}