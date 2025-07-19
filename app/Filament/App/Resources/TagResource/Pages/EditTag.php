<?php

namespace App\Filament\App\Resources\TagResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
