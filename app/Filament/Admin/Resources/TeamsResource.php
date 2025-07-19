<?php

namespace App\Filament\Admin\Resources;

use Filament\Schemas\Schema;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\TeamsResource\Pages\ListTeams;
use App\Filament\Admin\Resources\TeamsResource\Pages\CreateTeams;
use App\Filament\Admin\Resources\TeamsResource\Pages\EditTeams;
use App\Filament\Admin\Resources\TeamsResource\Pages;
use App\Filament\Admin\Resources\TeamsResource\RelationManagers;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamsResource extends Resource
{ 
    protected static ?string $model = Team::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Teams';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->autocapitalize('words')->required(),
                Select::make('user_id')->label('Owner')->options(
                    User::all()->pluck('name', 'id')
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
            'index' => ListTeams::route('/'),
            'create' => CreateTeams::route('/create'),
            'edit' => EditTeams::route('/{record}/edit'),
        ];
    }
}
