<?php

namespace App\Filament\Resources\CollectionItems\Pages;

use App\Filament\Resources\CollectionItems\CollectionItemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCollectionItem extends ViewRecord
{
    protected static string $resource = CollectionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
