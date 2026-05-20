<?php

namespace App\Filament\Resources\CollectionItems\Pages;

use App\Filament\Resources\CollectionItems\CollectionItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCollectionItem extends EditRecord
{
    protected static string $resource = CollectionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
