<?php

namespace App\Filament\App\Resources;

use App\Models\ContentBlock;
use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use App\Filament\App\Resources\ContentBlockResource\Pages;

class ContentBlockResource extends Resource
{
    protected static ?string $model = ContentBlock::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string | \UnitEnum | null $navigationGroup = 'Content Management';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->options(array_map(fn($type) => $type['name'], ContentBlock::getAvailableTypes()))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set, $state) => 
                        $set('category', ContentBlock::getAvailableTypes()[$state]['category'] ?? null)
                    ),

                Select::make('category')
                    ->options([
                        'content' => 'Content',
                        'media' => 'Media',
                        'layout' => 'Layout',
                        'interactive' => 'Interactive',
                        'advanced' => 'Advanced',
                    ]),

                Textarea::make('description')
                    ->rows(3),

                Forms\Components\RichEditor::make('content')
                    ->label('Block Content')
                    ->columnSpanFull(),

                Forms\Components\KeyValue::make('settings')
                    ->label('Block Settings')
                    ->keyLabel('Setting Name')
                    ->valueLabel('Setting Value'),

                FileUpload::make('preview_image')
                    ->label('Preview Image')
                    ->image()
                    ->directory('content-blocks'),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'text' => 'primary',
                        'image' => 'success',
                        'video' => 'warning',
                        'gallery' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('category')
                    ->sortable()
                    ->badge(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_map(fn($type) => $type['name'], ContentBlock::getAvailableTypes())),

                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'content' => 'Content',
                        'media' => 'Media',
                        'layout' => 'Layout',
                        'interactive' => 'Interactive',
                        'advanced' => 'Advanced',
                    ]),

                Tables\Filters\Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('duplicate')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(fn (ContentBlock $record) => $record->duplicate()),
                    Tables\Actions\Action::make('toggle_active')
                        ->icon('heroicon-o-power')
                        ->action(fn (ContentBlock $record) => $record->update(['is_active' => !$record->is_active]))
                        ->color(fn (ContentBlock $record) => $record->is_active ? 'danger' : 'success')
                        ->label(fn (ContentBlock $record) => $record->is_active ? 'Deactivate' : 'Activate'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->icon('heroicon-o-check')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentBlocks::route('/'),
            'create' => Pages\CreateContentBlock::route('/create'),
            'edit' => Pages\EditContentBlock::route('/{record}/edit'),
        ];
    }
}