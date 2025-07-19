<?php

namespace App\Filament\Admin\Resources\GlobalSettingsResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\GlobalSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobalSettings extends EditRecord
{
    protected static string $resource = GlobalSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
