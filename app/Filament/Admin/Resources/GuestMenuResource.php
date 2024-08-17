<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GuestMenuResource\Pages;
use App\Filament\Admin\Resources\GuestMenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuestMenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Guest Configuration';
    protected static ?string $navigationLabel = 'Menu list managment';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->autocapitalize('words'),
                TextInput::make('url'),
                TextInput::make('parent_id'),
                TextInput::make('order'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('name'),
                TextColumn::make('url'),
                TextColumn::make('parent_id'),
                TextColumn::make('order'),
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
            'index' => Pages\ListGuestMenus::route('/'),
            'create' => Pages\CreateGuestMenu::route('/create'),
            'edit' => Pages\EditGuestMenu::route('/{record}/edit'),
        ];
    }
}
