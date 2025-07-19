<?php

namespace App\Filament\App\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\AuthorResource\Pages\ListAuthors;
use App\Filament\App\Resources\AuthorResource\Pages\CreateAuthor;
use App\Filament\App\Resources\AuthorResource\Pages\EditAuthor;
use Filament\Forms;
use Filament\Tables;
use App\Models\Author;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\AuthorResource\Pages;
use App\Filament\App\Resources\AuthorResource\RelationManagers;
use Filament\Tables\Columns\TextColumn;

class AuthorResource extends Resource
{
    protected static ?string $model = Author::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('author_name')
                    ->autofocus()
                    ->required()
                    ->max(255),
                TextInput::make('author_last_name')
                    ->required()
                    ->max(255),
                TextInput::make('author_email')
                    ->email()
                    ->required(),
                TextInput::make('author_phone')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('author_name')
                ->sortable()
                ->searchable(),
                TextColumn::make('author_last_name')
                ->sortable()
                ->searchable(),
                TextColumn::make('author_email')
                ->sortable()
                ->searchable(),
                TextColumn::make('author_phone')
                ->sortable()
                ->searchable(),
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
            'index' => ListAuthors::route('/'),
            'create' => CreateAuthor::route('/create'),
            'edit' => EditAuthor::route('/{record}/edit'),
        ];
    }
}
