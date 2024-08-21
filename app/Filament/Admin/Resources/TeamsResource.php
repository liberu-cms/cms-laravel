<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TeamsResource\Pages;
use App\Filament\Admin\Resources\TeamsResource\RelationManagers;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamsResource extends Resource
{ 
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Teams';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->autocapitalize('words')->required(),
                Select::make('user_id')->label('Owner')->options(
                    \App\Models\User::all()->pluck('name', 'id')
                )->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('name'),
                TextColumn::make('owner.name'),
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
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeams::route('/create'),
            'edit' => Pages\EditTeams::route('/{record}/edit'),
        ];
    }
}
