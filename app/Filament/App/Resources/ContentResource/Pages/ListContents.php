<?php

namespace App\Filament\App\Resources\ContentResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\ContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContents extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
