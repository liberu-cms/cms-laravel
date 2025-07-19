<?php

namespace App\Filament\Admin\Resources\GlobalSettingsResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\GlobalSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlobalSettings extends ListRecords
{
    protected static string $resource = GlobalSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
