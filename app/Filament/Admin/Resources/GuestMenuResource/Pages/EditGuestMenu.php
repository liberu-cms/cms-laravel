<?php

namespace App\Filament\Admin\Resources\GuestMenuResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Admin\Resources\GuestMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuestMenu extends EditRecord
{
    protected static string $resource = GuestMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
