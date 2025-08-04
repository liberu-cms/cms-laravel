<?php

namespace App\Filament\App\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\ActivationResource\Pages\ListActivations;
use App\Filament\App\Resources\ActivationResource\Pages\CreateActivation;
use App\Filament\App\Resources\ActivationResource\Pages\EditActivation;
use App\Filament\App\Resources\ActivationResource\Pages;
use App\Models\Activation;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Define the form schema for creating and editing activations.
     *
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListActivations::route('/'),
            'create' => CreateActivation::route('/create'),
            'edit' => EditActivation::route('/{record}/edit'),
        ];
    }
}
