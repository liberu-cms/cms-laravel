<?php

namespace App\Filament\App\Resources\PageResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\App\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}