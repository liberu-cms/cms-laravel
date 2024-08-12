<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
/**
 * PageResource class.
 *
 * This class defines the Filament resource for managing pages, including form and table configurations.
 */
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\TextInput::make('title')
    //                 ->required()
    //                 ->maxLength(255),
    //             Forms\Components\Textarea::make('content')
    //                 ->required(),
    //             Forms\Components\TextInput::make('slug')
    //                 ->required()
    //                 ->maxLength(255),
    //             Forms\Components\DateTimePicker::make('published_at')
    //                 ->required(),
    //             Forms\Components\Select::make('user_id')
    //                 ->relationship('user', 'name')
    //                 ->required(),
    //             Forms\Components\Select::make('category_id')
    //                 ->relationship('category', 'name')
    //                 ->required(),
    //             Forms\Components\TagsInput::make('tags')
    //                 ->relationship('tags', 'name'),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
    /**
     * Configures the table used for listing pages.
     *
     * @param Table $table The table object to be configured.
     * @return Table The configured table object with columns for the page's title, slug, published date, author, and category.
     */
    /**
     * Returns an array of page routes for the resource.
     *
     * @return array The array of page routes.
     */
