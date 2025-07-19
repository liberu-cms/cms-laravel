<?php

namespace App\Filament\Admin\Resources\GuestMenuResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Admin\Resources\GuestMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuestMenus extends ListRecords
{
    protected static string $resource = GuestMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
