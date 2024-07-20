<?php

namespace App\Filament\App\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Author;
use Filament\Forms\Form;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAuthors::route('/'),
            'create' => Pages\CreateAuthor::route('/create'),
            'edit' => Pages\EditAuthor::route('/{record}/edit'),
        ];
    }
}
