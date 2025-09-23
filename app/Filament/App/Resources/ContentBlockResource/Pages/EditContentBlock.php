<?php

 namespace App\Filament\App\Resources\ContentBlockResource\Pages;

 use App\Filament\App\Resources\ContentBlockResource;
 use Filament\Actions\DeleteAction;
 use Filament\Resources\Pages\EditRecord;

 class EditContentBlock extends EditRecord
 {
     protected static string $resource = ContentBlockResource::class;

     protected function getHeaderActions(): array
     {
         return [
             DeleteAction::make(),
         ];
     }
 }