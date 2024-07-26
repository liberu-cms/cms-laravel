<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Content;

use Illuminate\Support\Facades\Cache;

class ContentComponent extends Component
{
    public function render()
    {
        $contents = Cache::remember('latest_contents', now()->addMinutes(15), function () {
            return Content::latest()->take(5)->get();
        });
        return view('livewire.content-component', compact('contents'));
    }
}