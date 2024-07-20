<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Content;

class ContentComponent extends Component
{
    public function render()
    {
        $contents = Content::latest()->take(5)->get();
        return view('livewire.content-component', compact('contents'));
    }
}