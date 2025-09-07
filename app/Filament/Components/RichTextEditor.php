<?php

namespace App\Filament\Components;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Component;

class RichTextEditor extends RichEditor
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->toolbarButtons([
            'attachFiles',
            'blockquote',
            'bold',
            'bulletList',
            'codeBlock',
            'h2',
            'h3',
            'italic',
            'link',
            'orderedList',
            'redo',
            'strike',
            'underline',
            'undo',
            'table',
            'media',
            'shortcodes',
        ]);

        $this->extraInputAttributes([
            'style' => 'min-height: 400px;'
        ]);
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function withShortcodes(): static
    {
        $this->extraInputAttributes([
            'data-shortcodes' => 'true'
        ]);

        return $this;
    }

    public function withMediaLibrary(): static
    {
        $this->extraInputAttributes([
            'data-media-library' => 'true'
        ]);

        return $this;
    }
}