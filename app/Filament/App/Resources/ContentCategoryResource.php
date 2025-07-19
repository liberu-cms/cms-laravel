<?php

namespace App\Filament\App\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\ContentCategoryResource\Pages\ListContentCategories;
use App\Filament\App\Resources\ContentCategoryResource\Pages\CreateContentCategory;
use App\Filament\App\Resources\ContentCategoryResource\Pages\EditContentCategory;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ContentCategory;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\ContentCategoryResource\Pages;
use App\Filament\App\Resources\ContentCategoryResource\RelationManagers;

class ContentCategoryResource extends Resource
{
    protected static ?string $model = ContentCategory::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('content_category_name')
                ->label('Content Category Name')
                ->required()
                ->max(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content_category_name')
                    ->label('Content Category Name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListContentCategories::route('/'),
            'create' => CreateContentCategory::route('/create'),
            'edit' => EditContentCategory::route('/{record}/edit'),
        ];
    }
}
