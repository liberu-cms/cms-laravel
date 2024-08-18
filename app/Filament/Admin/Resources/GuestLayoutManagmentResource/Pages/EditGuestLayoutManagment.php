<?php

namespace App\Filament\Admin\Resources\GuestLayoutManagmentResource\Pages;

use App\Filament\Admin\Resources\GuestLayoutManagmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuestLayoutManagment extends EditRecord
{
    protected static string $resource = GuestLayoutManagmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
