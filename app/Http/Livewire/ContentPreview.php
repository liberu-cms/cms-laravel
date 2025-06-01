<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Content;

class ContentPreview extends Component
{
    public $content;
    public $previewHtml;

    protected $listeners = ['updatePreview', 'contentUpdated'];

    public function mount(Content $content = null)
    {
        $this->content = $content ?? new Content();
        $this->updatePreview();
    }

    public function updatePreview()
    {
        // For block-based content, we'll render all blocks
        if ($this->content->contentBlocks()->count() > 0) {
            $this->previewHtml = $this->content->renderBlocks();
        } else {
            // Fallback to the body field for backward compatibility
            $this->previewHtml = $this->content->body ?? '';
        }
    }

    public function contentUpdated($content = null, $title = null)
    {
        // This method can be used to update the preview when content changes
        // but we're now using the block-based approach
        $this->updatePreview();
    }

    public function render()
    {
        return view('livewire.content-preview');
    }
}