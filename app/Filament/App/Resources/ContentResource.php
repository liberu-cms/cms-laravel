
<?php

namespace App\Filament\App\Resources;

use App\Models\Content;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BelongsToColumn;
use App\Filament\App\Resources\ContentResource\Pages;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('content_title')
                    ->label('Content Title')
                    ->required(),
                Forms\Components\Select::make('author_id')
                    ->relationship('author', 'author_name')
                    ->label('Author')
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'content_category_name')
                    ->label('Category')
                    ->required(),
                Forms\Components\RichEditor::make('content_body')
                    ->label('Content Body')
                    ->required(),
                Forms\Components\Select::make('content_status')
                    ->label('Content Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                Forms\Components\FileUpload::make('featured_image_url')
                    ->label('Featured Image')
                    ->image(),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Is Featured'),
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
