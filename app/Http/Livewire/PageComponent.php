<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Page;

class PageComponent extends Component
{
    public function render()
    {
        $pages = Page::latest()->take(5)->get();
        return view('livewire.page-component', compact('pages'));
    }
}