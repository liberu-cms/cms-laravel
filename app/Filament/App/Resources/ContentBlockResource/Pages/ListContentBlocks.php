<?php

 namespace App\Filament\App\Resources\ContentBlockResource\Pages;

 use App\Filament\App\Resources\ContentBlockResource;
 use Filament\Actions\CreateAction;
 use Filament\Resources\Pages\ListRecords;

 class ListContentBlocks extends ListRecords
 {
     protected static string $resource = ContentBlockResource::class;

     protected function getHeaderActions(): array
     {
         return [
             CreateAction::make(),
         ];
     }
 }