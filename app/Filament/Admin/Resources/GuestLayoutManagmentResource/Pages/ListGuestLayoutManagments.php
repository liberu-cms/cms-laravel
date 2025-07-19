<?php

namespace App\Filament\Admin\Resources\GuestLayoutManagmentResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\GuestLayoutManagmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuestLayoutManagments extends ListRecords
{
    protected static string $resource = GuestLayoutManagmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
