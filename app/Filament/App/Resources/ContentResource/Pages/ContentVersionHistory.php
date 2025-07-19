<?php

namespace App\Filament\App\Resources\ContentResource\Pages;

use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use App\Filament\App\Resources\ContentResource;
use App\Models\Content;
use App\Models\ContentVersion;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;

class ContentVersionHistory extends Page
{
    protected static string $resource = ContentResource::class;

    protected string $view = 'filament.app.resources.content-resource.pages.content-version-history';

    public Content $record;

    public function table(Table $table): Table
    {
        return $table
            ->query(ContentVersion::query()->where('content_id', $this->record->id))
            ->columns([
                TextColumn::make('version_number')
                    ->sortable(),
                TextColumn::make('title')
                    ->limit(50),
                TextColumn::make('author.name')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->url(fn (ContentVersion $record): string => route('filament.app.resources.contents.version-preview', ['record' => $this->record, 'version' => $record]))
                    ->openUrlInNewTab(),
                Action::make('compare')
                    ->action(function (ContentVersion $record) {
                        $this->emit('openCompareModal', $record->id);
                    })
                    ->icon('heroicon-o-document-duplicate'),
                Action::make('rollback')
                    ->action(function (ContentVersion $record) {
                        $this->record->rollbackToVersion($record);
                        $this->notify('success', 'Content rolled back to version ' . $record->version_number);
                    })
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkAction::make('compare')
                    ->action(function (array $records) {
                        $recordIds = collect($records)->pluck('id')->toArray();
                        if (count($recordIds) === 2) {
                            $this->emit('compareVersions', $recordIds[0], $recordIds[1]);
                        } else {
                            $this->notify('danger', 'Please select exactly 2 versions to compare');
                        }
                    })
                    ->deselectRecordsAfterCompletion()
                    ->icon('heroicon-o-document-duplicate')
                    ->label('Compare Selected')
                    ->requiresConfirmation(false),
            ])
            ->defaultSort('version_number', 'desc');
    }
}