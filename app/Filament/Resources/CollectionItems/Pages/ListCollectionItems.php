<?php

namespace App\Filament\Resources\CollectionItems\Pages;

use App\Filament\Resources\CollectionItems\CollectionItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCollectionItems extends ListRecords
{
    protected static string $resource = CollectionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
