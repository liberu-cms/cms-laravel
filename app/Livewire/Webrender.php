<?php

namespace App\Livewire;

use Livewire\Component;

class Webrender extends Component
{
    protected $contents;
    public function mount(){
        $this->contents = session('contents');
    }

    public function render()
    {
        // dd($this->contents);
        return view('livewire.webrender', [
            'contents' => $this->contents
        ]);
    }
}
