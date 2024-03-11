<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Author;
use App\Models\Content;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ContentCategory;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\BooleanColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Admin\Resources\ContentResource\Pages;
use Filament\Tables\Columns\Summarizers\Concerns\BelongsToColumn;
use App\Filament\Admin\Resources\ContentResource\RelationManagers;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('content_title')
                    ->label('Content Title')
                    ->required()
                    ->max(255),
                Forms\Components\Textarea::make('content_body')
                    ->label('Content Body')
                    ->required()
                    ->max(65535),
                Forms\Components\Select::make('author_id')
                    ->label('Author')
                    ->required()
                    ->options(Author::pluck('author_name', 'author_id')),
                Forms\Components\DatePicker::make('published_date')
                    ->label('Published Date')
                    ->required(),
                Forms\Components\Select::make('content_type')
                    ->label('Content Type')
                    ->required()
                    ->options([
                        'article' => 'Article',
                        'page' => 'Page',
                        'post' => 'Post',
                    ]),
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->required()
                    ->options(ContentCategory::pluck('content_category_name', 'content_category_id')),
                Forms\Components\Select::make('content_status')
                    ->label('Content Status')
                    ->required()
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                ImageColumn::make('featured_image_url')
                    ->label('Featured Image URL'),
                Forms\Components\TagsInput::make('tag')
                    ->label('Tags')
                    ->separator(',')
                    ->relationship('tag', 'tag_name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content_title')
                    ->label('Content Title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author.author_name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                BelongsToColumn::make('category.content_category_name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('content_status')
                    ->label('Content Status')
                    ->colors([
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'danger',
                    ]),
                BooleanColumn::make('is_featured')
                    ->label('Is Featured')
                    ->trueValue(true)
                    ->falseValue(false)
                    ->sortable(),
                ImageColumn::make('featured_image_url')
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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
