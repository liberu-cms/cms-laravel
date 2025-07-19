<?php

namespace App\Filament\Admin\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Admin\Resources\GlobalSettingsResource\Pages\ListGlobalSettings;
use App\Filament\Admin\Resources\GlobalSettingsResource\Pages\CreateGlobalSettings;
use App\Filament\Admin\Resources\GlobalSettingsResource\Pages\EditGlobalSettings;
use App\Filament\Admin\Resources\GlobalSettingsResource\Pages;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Intelrx\Sitesettings\Models\SiteSettings;

class GlobalSettingsResource extends Resource
{
    protected static ?string $model = SiteSettings::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')->label('Title')->required(),
                TextInput::make('value')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('Title'),
                TextColumn::make('value')->label('Value'),
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
            'index' => ListGlobalSettings::route('/'),
            'create' => CreateGlobalSettings::route('/create'),
            'edit' => EditGlobalSettings::route('/{record}/edit'),
        ];
    }
}
