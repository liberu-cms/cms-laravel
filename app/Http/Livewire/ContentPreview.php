<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Content;

class ContentPreview extends Component
{
    public $content;
    public $previewHtml;

    protected $listeners = ['updatePreview'];

    public function mount(Content $content)
    {
        $this->content = $content;
        $this->updatePreview();
    }

    public function updatePreview($content = null)
    {
        if ($content !== null) {
            $this->content->content_body = $content;
        }
        // Convert markdown to HTML or apply any other necessary transformations
        $this->previewHtml = $this->content->content_body;
    }

    public function render()
    {
        return view('livewire.content-preview');
    }
}