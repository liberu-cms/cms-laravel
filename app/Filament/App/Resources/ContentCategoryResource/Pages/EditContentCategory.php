<?php

namespace App\Filament\App\Resources\ContentCategoryResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\ContentCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContentCategory extends EditRecord
{
    protected static string $resource = ContentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
