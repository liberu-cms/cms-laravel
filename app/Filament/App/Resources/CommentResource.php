<?php

namespace App\Filament\App\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkAction;
use App\Filament\App\Resources\CommentResource\Pages\ListComments;
use App\Filament\App\Resources\CommentResource\Pages\CreateComment;
use App\Filament\App\Resources\CommentResource\Pages\EditComment;
use Filament\Forms;
use Filament\Tables;
use App\Models\Author;
use App\Models\Comment;
use App\Models\Content;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\BelongsToSelect;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\CommentResource\Pages;
use App\Filament\App\Resources\CommentResource\RelationManagers;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('content_id')
                    ->label('Content')
                    ->required()
                    ->options(Content::pluck('content_title', 'content_id'))
                    ->reactive(),
                Select::make('user_id')
                    ->label('User')
                    ->required()
                    ->relationship('user', 'name')
                    ->reactive(),
                Select::make('parent_id')
                    ->label('Reply To')
                    ->options(function (callable $get) {
                        $contentId = $get('content_id');
                        if (!$contentId) {
                            return [];
                        }
                        return Comment::where('content_id', $contentId)
                            ->where('status', Comment::STATUS_APPROVED)
                            ->pluck('body', 'id');
                    })
                    ->nullable(),
                Textarea::make('body')
                    ->required()
                    ->maxLength(1000),
                Select::make('status')
                    ->options([
                        Comment::STATUS_PENDING => 'Pending',
                        Comment::STATUS_APPROVED => 'Approved',
                        Comment::STATUS_REJECTED => 'Rejected',
                        Comment::STATUS_SPAM => 'Spam',
                    ])
                    ->required()
                    ->default(Comment::STATUS_PENDING),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('content.content_title')
                    ->label('Content')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('body')
                    ->label('Comment')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'spam' => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Comment::STATUS_PENDING => 'Pending',
                        Comment::STATUS_APPROVED => 'Approved',
                        Comment::STATUS_REJECTED => 'Rejected',
                        Comment::STATUS_SPAM => 'Spam',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Comment $record) => $record->status !== Comment::STATUS_APPROVED)
                    ->action(fn (Comment $record) => $record->update(['status' => Comment::STATUS_APPROVED])),
                Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Comment $record) => $record->status !== Comment::STATUS_REJECTED)
                    ->action(fn (Comment $record) => $record->update(['status' => Comment::STATUS_REJECTED])),
                Action::make('mark_as_spam')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (Comment $record) => $record->status !== Comment::STATUS_SPAM)
                    ->action(fn (Comment $record) => $record->update(['status' => Comment::STATUS_SPAM])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['status' => Comment::STATUS_APPROVED])),
                    BulkAction::make('reject_selected')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['status' => Comment::STATUS_REJECTED])),
                    BulkAction::make('mark_as_spam_selected')
                        ->label('Mark as Spam')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['status' => Comment::STATUS_SPAM])),
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
            'index' => ListComments::route('/'),
            'create' => CreateComment::route('/create'),
            'edit' => EditComment::route('/{record}/edit'),
        ];
    }
}
