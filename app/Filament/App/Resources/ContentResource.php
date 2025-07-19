<?php

namespace App\Filament\App\Resources;

use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\ContentResource\Pages\ListContents;
use App\Filament\App\Resources\ContentResource\Pages\CreateContent;
use App\Filament\App\Resources\ContentResource\Pages\EditContent;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ContentCategory;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\ContentResource\Pages;
use App\Filament\App\Resources\ContentResource\RelationManagers;

class ContentResource extends Resource
{
    // public static function form(Form $form): Form
    // {
    //     return [];
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('author.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    ->sortable(),
                TextColumn::make('status')
                    ->sortable(),
                TextColumn::make('published_at')
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
            ])
            ->modifyQueryUsing(fn($query) => $query->with('author'));
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
            'index' => ListContents::route('/'),
            'create' => CreateContent::route('/create'),
            'edit' => EditContent::route('/{record}/edit'),
        ];
    }
}
