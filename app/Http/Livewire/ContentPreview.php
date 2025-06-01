<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Content;

class ContentPreview extends Component
{
    public $content;
    public $previewHtml;

    protected $listeners = ['updatePreview'];

    public function mount(Content $content = null)
    {
        $this->content = $content ?? new Content();
        $this->updatePreview($this->content->body);
    }

    public function updatePreview($content = null)
    {
        // Convert markdown to HTML or apply any other necessary transformations
        $this->previewHtml = $content ?? $this->content->body ?? '';
    }

    public function render()
    {
        return view('livewire.content-preview');
    }
}