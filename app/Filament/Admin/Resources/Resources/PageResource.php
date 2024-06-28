<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('published_at')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('category_id')
    /**
     * Configures the form used for creating and editing pages.
     * 
     * @param Form $form The form object to be configured.
     * @return Form The configured form object with fields for the page's title, content, slug, published date, user, and category.
     */
                    ->relationship('category', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
            ])
            ->filters([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            Pages\ListPages::route('/'),
            Pages\CreatePage::route('/create'),
            Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function isDeferred(): bool
    {
        return false;
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
