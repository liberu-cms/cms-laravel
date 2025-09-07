<?php

namespace App\Filament\App\Resources;

use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\App\Resources\ContentResource\Pages\ListContents;
use App\Filament\App\Resources\ContentResource\Pages\CreateContent;
use App\Filament\App\Resources\ContentResource\Pages\EditContent;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Filament\Components\RichTextEditor;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\App\Resources\ContentResource\Pages;
use App\Filament\App\Resources\ContentResource\RelationManagers;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Content')
                    ->tabs([
                        Tabs\Tab::make('Content')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $context, $state, callable $set) => 
                                        $context === 'create' ? $set('slug', \Str::slug($state)) : null
                                    ),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Content::class, 'slug', ignoreRecord: true),

                                RichTextEditor::make('body')
                                    ->required()
                                    ->columnSpanFull()
                                    ->withShortcodes()
                                    ->withMediaLibrary(),

                                Select::make('type')
                                    ->options([
                                        'post' => 'Post',
                                        'page' => 'Page',
                                        'news' => 'News',
                                        'blog' => 'Blog',
                                    ])
                                    ->required(),

                                Select::make('status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Published',
                                        'scheduled' => 'Scheduled',
                                    ])
                                    ->required(),

                                DateTimePicker::make('published_at')
                                    ->label('Publish Date'),

                                FileUpload::make('featured_image_url')
                                    ->label('Featured Image')
                                    ->image()
                                    ->directory('featured-images'),
                            ]),

                        Tabs\Tab::make('SEO')
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(60)
                                    ->helperText('Recommended: 50-60 characters'),

                                Textarea::make('seo_description')
                                    ->label('Meta Description')
                                    ->maxLength(160)
                                    ->rows(3)
                                    ->helperText('Recommended: 120-160 characters'),

                                TextInput::make('seo_keywords')
                                    ->label('Focus Keywords')
                                    ->helperText('Comma-separated keywords'),

                                TextInput::make('canonical_url')
                                    ->label('Canonical URL')
                                    ->url(),
                            ]),

                        Tabs\Tab::make('Settings')
                            ->schema([
                                Select::make('workflow_status')
                                    ->options([
                                        'draft' => 'Draft',
                                        'review' => 'In Review',
                                        'approved' => 'Approved',
                                        'published' => 'Published',
                                        'scheduled' => 'Scheduled',
                                        'rejected' => 'Rejected',
                                    ])
                                    ->default('draft'),

                                DateTimePicker::make('scheduled_for')
                                    ->label('Schedule For'),

                                Select::make('author_id')
                                    ->relationship('author', 'name')
                                    ->searchable()
                                    ->preload(),

                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload(),

                                Select::make('template')
                                    ->options([
                                        'default' => 'Default',
                                        'full-width' => 'Full Width',
                                        'sidebar-left' => 'Sidebar Left',
                                        'sidebar-right' => 'Sidebar Right',
                                        'landing-page' => 'Landing Page',
                                    ])
                                    ->default('default'),

                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured Content'),

                                Forms\Components\Toggle::make('is_sticky')
                                    ->label('Sticky Post'),

                                Forms\Components\Toggle::make('allow_comments')
                                    ->label('Allow Comments')
                                    ->default(true),

                                Forms\Components\Toggle::make('password_protected')
                                    ->label('Password Protected')
                                    ->reactive(),

                                TextInput::make('content_password')
                                    ->label('Content Password')
                                    ->password()
                                    ->visible(fn (callable $get) => $get('password_protected')),
                            ]),

                        Tabs\Tab::make('Advanced')
                            ->schema([
                                Textarea::make('excerpt')
                                    ->label('Custom Excerpt')
                                    ->rows(3)
                                    ->helperText('Leave empty to auto-generate from content'),

                                Forms\Components\TagsInput::make('tags')
                                    ->label('Tags')
                                    ->separator(','),

                                Forms\Components\Repeater::make('custom_fields')
                                    ->label('Custom Fields')
                                    ->schema([
                                        TextInput::make('key')
                                            ->label('Field Name')
                                            ->required(),
                                        TextInput::make('value')
                                            ->label('Field Value')
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Select::make('related_content_ids')
                                    ->label('Related Content')
                                    ->multiple()
                                    ->options(function () {
                                        return Content::where('workflow_status', 'published')
                                            ->pluck('title', 'id');
                                    })
                                    ->searchable(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->limit(50),
                TextColumn::make('author.name')
                    ->sortable()
                    ->searchable()
                    ->label('Author'),
                TextColumn::make('type')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'post' => 'primary',
                        'page' => 'success',
                        'news' => 'warning',
                        'blog' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('workflow_status')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'approved' => 'info',
                        'published' => 'success',
                        'scheduled' => 'primary',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->label('Status'),
                TextColumn::make('word_count')
                    ->sortable()
                    ->label('Words')
                    ->formatStateUsing(fn ($state) => number_format($state)),
                TextColumn::make('reading_time')
                    ->sortable()
                    ->label('Read Time')
                    ->formatStateUsing(fn ($state) => $state . ' min'),
                TextColumn::make('content_score')
                    ->sortable()
                    ->label('Score')
                    ->formatStateUsing(fn ($state) => $state ? round($state) . '%' : 'N/A')
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger')),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                Tables\Columns\IconColumn::make('is_sticky')
                    ->boolean()
                    ->label('Sticky'),
                TextColumn::make('published_at')
                    ->sortable()
                    ->dateTime()
                    ->label('Published'),
                TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('workflow_status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'In Review',
                        'approved' => 'Approved',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'post' => 'Post',
                        'page' => 'Page',
                        'news' => 'News',
                        'blog' => 'Blog',
                    ]),
                Tables\Filters\SelectFilter::make('author')
                    ->relationship('author', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('is_featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Featured Only'),
                Tables\Filters\Filter::make('is_sticky')
                    ->query(fn (Builder $query): Builder => $query->where('is_sticky', true))
                    ->label('Sticky Only'),
                Tables\Filters\Filter::make('published_today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('published_at', today()))
                    ->label('Published Today'),
                Tables\Filters\Filter::make('published_this_week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('published_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label('Published This Week'),
            ])
            ->recordActions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('publish')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn (Content $record) => $record->publish())
                        ->visible(fn (Content $record) => !$record->isPublished()),
                    Tables\Actions\Action::make('feature')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn (Content $record) => $record->update(['is_featured' => !$record->is_featured]))
                        ->label(fn (Content $record) => $record->is_featured ? 'Unfeature' : 'Feature'),
                    Tables\Actions\Action::make('duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Content $record) {
                            $newContent = $record->replicate();
                            $newContent->title = $record->title . ' (Copy)';
                            $newContent->slug = $record->generateUniqueSlug($newContent->title);
                            $newContent->workflow_status = 'draft';
                            $newContent->published_at = null;
                            $newContent->save();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->publish())
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('feature')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn (Collection $records) => Content::bulkFeature($records->pluck('id')->toArray(), true))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->icon('heroicon-o-star')
                        ->color('gray')
                        ->action(fn (Collection $records) => Content::bulkFeature($records->pluck('id')->toArray(), false))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('change_status')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            Select::make('workflow_status')
                                ->options([
                                    'draft' => 'Draft',
                                    'review' => 'In Review',
                                    'approved' => 'Approved',
                                    'published' => 'Published',
                                    'scheduled' => 'Scheduled',
                                    'rejected' => 'Rejected',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            Content::bulkUpdateStatus($records->pluck('id')->toArray(), $data['workflow_status']);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->modifyQueryUsing(fn($query) => $query->with('author'));
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
            'index' => ListContents::route('/'),
            'create' => CreateContent::route('/create'),
            'edit' => EditContent::route('/{record}/edit'),
        ];
    }
}
