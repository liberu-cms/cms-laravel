<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\GlobalSettingsResource\Pages;
use App\Filament\Admin\Resources\GlobalSettingsResource\RelationManagers;
use App\Models\GlobalSettings;
use App\Models\SiteSettings;
use Faker\Provider\ar_EG\Text;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GlobalSettingsResource extends Resource
{
    protected static ?string $model = SiteSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Settings';

    public static function form(Form $form): Form
    {
        // $table->string('name')->nullable();
        //     $table->string('currency')->nullable();
        //     $table->string('default_language')->nullable();
        //     $table->text('address')->nullable();
        //     $table->string('country')->nullable();
        //     $table->string('email')->nullable();
        //     $table->string('phone_01')->nullable();
        //     $table->string('phone_02')->nullable();
        //     $table->string('phone_03')->nullable();
        //     $table->string('phone_04')->nullable();
        //     $table->string('facebook')->nullable();
        //     $table->string('twitter')->nullable();
        //     $table->string('github')->nullable();
        //     $table->string('youtube')->nullable();
        //     $table->decimal('sales_commission_percentage', 5, 2)->default(1.00);
        //     $table->decimal('lettings_commission_percentage', 5, 2)->default(8.00);
        return $form
            ->schema([
                TextInput::make('name')->autocapitalize('words')->required(),
                TextInput::make('currency')->autocapitalize('words')->required(),
                TextInput::make('default_language')->autocapitalize('words')->required(),
                TextInput::make('address')->autocapitalize('words')->required(),
                TextInput::make('country')->autocapitalize('words')->required(),
                TextInput::make('email')->autocapitalize('words')->required(),
                TextInput::make('phone_01')->autocapitalize('words')->required(),
                TextInput::make('phone_02')->autocapitalize('words'),
                TextInput::make('phone_03')->autocapitalize('words'),
                TextInput::make('phone_04')->autocapitalize('words'),
                TextInput::make('facebook')->autocapitalize('words')->required(),
                TextInput::make('twitter')->autocapitalize('words')->required(),
                TextInput::make('github')->autocapitalize('words')->required(),
                TextInput::make('youtube')->autocapitalize('words')->required(),
                TextInput::make('sales_commission_percentage')->autocapitalize('words')->required(),
                TextInput::make('lettings_commission_percentage')->autocapitalize('words')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('currency'),
                TextColumn::make('default_language')->label('language'),
                TextColumn::make('country'),
                TextColumn::make('email'),
                TextColumn::make('phone_01')->label('Phone'),
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
            'index' => Pages\ListGlobalSettings::route('/'),
            'create' => Pages\CreateGlobalSettings::route('/create'),
            'edit' => Pages\EditGlobalSettings::route('/{record}/edit'),
        ];
    }
}
