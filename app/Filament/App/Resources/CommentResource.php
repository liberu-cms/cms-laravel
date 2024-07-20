<?php

namespace App\Filament\App\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Author;
use App\Models\Comment;
use App\Models\Content;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\BelongsToSelect;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\CommentResource\Pages;
use App\Filament\App\Resources\CommentResource\RelationManagers;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('content_id')
                ->label('Content')
                ->required()
                ->options(Content::pluck('content_title', 'content_id'))
                ->reactive(),
            Select::make('author_id')
                ->label('Author')
                ->required()
                ->options(Author::pluck('author_name', 'author_id'))
                ->reactive(),
            TextInput::make('comment_body')
                ->required()
                ->max(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content.content_title')
                ->label('Content')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('author.author_name')
                ->label('Author')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('comment_body')
                ->label('Comment Body')
                ->searchable()
                ->sortable(),
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
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
