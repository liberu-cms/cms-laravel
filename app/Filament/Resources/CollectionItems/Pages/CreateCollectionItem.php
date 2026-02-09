<?php

namespace App\Filament\Resources\CollectionItems\Pages;

use App\Filament\Resources\CollectionItems\CollectionItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollectionItem extends CreateRecord
{
    protected static string $resource = CollectionItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
