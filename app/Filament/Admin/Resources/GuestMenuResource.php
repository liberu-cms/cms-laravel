<?php

namespace App\Filament\Admin\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\GuestMenuResource\Pages\ListGuestMenus;
use App\Filament\Admin\Resources\GuestMenuResource\Pages\CreateGuestMenu;
use App\Filament\Admin\Resources\GuestMenuResource\Pages\EditGuestMenu;
use App\Filament\Admin\Resources\GuestMenuResource\Pages;
use App\Filament\Admin\Resources\GuestMenuResource\RelationManagers;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuestMenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Guest Configuration';
    protected static ?string $navigationLabel = 'Menu list managment';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->autocapitalize('words')->required(),
                TextInput::make('url')->required(),
                TextInput::make('parent_id'),
                TextInput::make('order')->required(),
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
            'index' => ListGuestMenus::route('/'),
            'create' => CreateGuestMenu::route('/create'),
            'edit' => EditGuestMenu::route('/{record}/edit'),
        ];
    }
}
