<?php

namespace App\Filament\Admin\Resources\TeamsResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\TeamsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeams extends EditRecord
{
    protected static string $resource = TeamsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
