<?php

namespace App\Filament\App\Resources\CommentResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\App\Resources\CommentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComment extends EditRecord
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
