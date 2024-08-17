<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GuestLayoutManagmentResource\Pages;
use App\Filament\Admin\Resources\GuestLayoutManagmentResource\RelationManagers;
use App\Models\GuestLayoutManagment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuestLayoutManagmentResource extends Resource
{
    protected static ?string $model = GuestLayoutManagment::class;
    protected static ?string $navigationGroup = 'Guest Configuration';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Layout managment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListGuestLayoutManagments::route('/'),
            'create' => Pages\CreateGuestLayoutManagment::route('/create'),
            'edit' => Pages\EditGuestLayoutManagment::route('/{record}/edit'),
        ];
    }
}
