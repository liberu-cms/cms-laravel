<?php

namespace App\Filament\Resources\CollectionItems;

use App\Filament\Resources\CollectionItems\Pages\CreateCollectionItem;
use App\Filament\Resources\CollectionItems\Pages\EditCollectionItem;
use App\Filament\Resources\CollectionItems\Pages\ListCollectionItems;
use App\Filament\Resources\CollectionItems\Pages\ViewCollectionItem;
use App\Filament\Resources\CollectionItems\Schemas\CollectionItemForm;
use App\Filament\Resources\CollectionItems\Schemas\CollectionItemInfolist;
use App\Filament\Resources\CollectionItems\Tables\CollectionItemsTable;
use App\Models\CollectionItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CollectionItemResource extends Resource
{
    protected static ?string $model = CollectionItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $modelLabel = 'Content';

    protected static ?string $pluralModelLabel = 'Content';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CollectionItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CollectionItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollectionItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollectionItems::route('/'),
            'create' => CreateCollectionItem::route('/create'),
            'view' => ViewCollectionItem::route('/{record}'),
            'edit' => EditCollectionItem::route('/{record}/edit'),
        ];
    }
}
