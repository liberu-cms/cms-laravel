<?php

namespace App\Filament\App\Resources\ContentResource\Pages;

use App\Filament\App\Resources\ContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('version_history')
                ->url(fn () => $this->getResource()::getUrl('version-history', ['record' => $this->record]))
                ->icon('heroicon-o-clock')
                ->label('Version History'),
            Actions\Action::make('save_draft')
                ->action(fn () => $this->saveAsDraft())
                ->icon('heroicon-o-document')
                ->label('Save as Draft'),
            Actions\Action::make('publish')
                ->action(fn () => $this->publish())
                ->icon('heroicon-o-paper-airplane')
                ->label('Publish'),
        ];
    }

    #[On('updatePreview')]
    public function updatePreview($content)
    {
        // This method will be called when the content is updated
        // You can perform any necessary transformations here
        $this->dispatch('updatePreview', $content);
    }

    protected function afterSave(): void
    {
        // Create a new version after saving the content
        $this->record->createVersion();
    }

    public function saveAsDraft()
    {
        $this->record->is_draft = true;
        $this->record->save();
        $this->record->createVersion();

        $this->notify('success', 'Content saved as draft');
    }

    public function publish()
    {
        $this->record->is_draft = false;
        $this->record->status = 'published';
        $this->record->published_at = now();
        $this->record->save();
        $this->record->createVersion();

        $this->notify('success', 'Content published successfully');
    }

    public function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save')
                ->extraAttributes(['wire:click' => 'saveAsDraft']),
            $this->getPublishFormAction(),
        ];
    }

    protected function getPublishFormAction(): Actions\Action
    {
        return Actions\Action::make('publish')
            ->label('Publish')
            ->action('publish')
            ->color('success');
    }

    public function mount($record): void
    {
        parent::mount($record);

        // Set up autosave
        $this->dispatch('contentEditorMounted', [
            'interval' => 30000, // 30 seconds
            'recordId' => $this->record->id,
        ]);
    }
}