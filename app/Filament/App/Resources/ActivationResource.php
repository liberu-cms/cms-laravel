<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ActivationResource\Pages;
use App\Models\Activation;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Class ActivationResource
 *
 * This class defines the Filament resource for managing activations.
 */
class ActivationResource extends Resource
{
    protected static ?string $model = Activation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Define the form schema for creating and editing activations.
     *
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Add form fields here
            ]);
    }

    /**
     * Define the table structure for displaying activations.
     *
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Add table columns here
            ])
            ->filters([
                // Add filters here
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

    /**
     * Define the relations for the Activation model.
     *
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            // Add relations here
        ];
    }

    /**
     * Define the pages for the Activation resource.
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivations::route('/'),
            'create' => Pages\CreateActivation::route('/create'),
            'edit' => Pages\EditActivation::route('/{record}/edit'),
        ];
    }
}
