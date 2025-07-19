<?php

namespace App\Filament\Admin\Resources\TeamsResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\TeamsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeams extends ListRecords
{
    protected static string $resource = TeamsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
