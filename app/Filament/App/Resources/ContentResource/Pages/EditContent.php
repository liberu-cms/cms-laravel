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
        ];
    }

    #[On('updatePreview')]
    public function updatePreview($content)
    {
        // This method will be called when the content is updated
        // You can perform any necessary transformations here
        $this->emit('updatePreview', $content);
    }

    protected function afterSave(): void
    {
        // Create a new version after saving the content
        $this->record->createVersion();
    }
}