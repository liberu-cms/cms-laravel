<?php

namespace App\Filament\App\Resources\ContentCategoryResource\Pages;

use App\Filament\App\Resources\ContentCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContentCategories extends ListRecords
{
    protected static string $resource = ContentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
