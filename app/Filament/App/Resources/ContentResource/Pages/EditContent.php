<?php

namespace App\Filament\App\Resources\ContentResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\View;
use App\Filament\App\Resources\ContentResource;
use App\Models\Content;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use Filament\Forms;
use Filament\Notifications\Notification;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            DeleteAction::make(),
            Action::make('version_history')
                ->url(fn () => $this->getResource()::getUrl('version-history', ['record' => $this->record]))
                ->icon('heroicon-o-clock')
                ->label('Version History'),
        ];

        // Add workflow actions based on current status
        if ($this->record->isDraft()) {
            $actions[] = Action::make('submit_for_review')
                ->label('Submit for Review')
                ->icon('heroicon-o-paper-airplane')
                ->schema([
                    Select::make('review_by')
                        ->label('Assign Reviewer')
                        ->options(function () {
                            return User::permission('review_content')
                                ->where('id', '!=', auth()->id())
                                ->pluck('name', 'id');
                        })
                        ->searchable()
                ])
                ->action(function (array $data): void {
                    $this->record->submitForReview($data['review_by'] ?? null);
                    Notification::make()
                        ->title('Content submitted for review')
                        ->success()
                        ->send();
                });
        }

        if ($this->record->isInReview() && auth()->user()->can('approve', $this->record)) {
            $actions[] = Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function (): void {
                    $this->record->approve();
                    Notification::make()
                        ->title('Content approved')
                        ->success()
                        ->send();
                });

            $actions[] = Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->schema([
                    Textarea::make('rejection_reason')
                        ->label('Reason for Rejection')
                        ->required()
                ])
                ->action(function (array $data): void {
                    $this->record->reject();
                    // Store rejection reason if needed
                    Notification::make()
                        ->title('Content rejected')
                        ->danger()
                        ->send();
                });
        }

        if ($this->record->isApproved()) {
            $actions[] = Action::make('publish')
                ->label('Publish Now')
                ->icon('heroicon-o-globe-alt')
                ->color('success')
                ->action(function (): void {
                    $this->record->publish();
                    Notification::make()
                        ->title('Content published')
                        ->success()
                        ->send();
                });

            $actions[] = Action::make('schedule')
                ->label('Schedule')
                ->icon('heroicon-o-calendar')
                ->schema([
                    DateTimePicker::make('scheduled_for')
                        ->label('Schedule Publication For')
                        ->required()
                        ->minDate(now())
                ])
                ->action(function (array $data): void {
                    $this->record->schedule($data['scheduled_for']);
                    Notification::make()
                        ->title('Content scheduled for publication')
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }

    protected function getFormSchema(): array
    {
        $schema = parent::getFormSchema();

        // Add SEO tab to the form
        $schema[] = Tab::make('SEO')
            ->schema([
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->placeholder('Enter meta title')
                    ->helperText('Recommended length: 50-60 characters')
                    ->maxLength(60)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                        $livewire->emit('seoDataUpdated',
                            $state,
                            $livewire->data['meta_description'] ?? '',
                            $livewire->data['meta_keywords'] ?? '',
                            $livewire->data['canonical_url'] ?? ''
                        );
                    }),

                Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->placeholder('Enter meta description')
                    ->helperText('Recommended length: 150-160 characters')
                    ->maxLength(160)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                        $livewire->emit('seoDataUpdated',
                            $livewire->data['meta_title'] ?? '',
                            $state,
                            $livewire->data['meta_keywords'] ?? '',
                            $livewire->data['canonical_url'] ?? ''
                        );
                    }),

                TextInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->placeholder('keyword1, keyword2, keyword3')
                    ->helperText('Separate keywords with commas. Recommended: 3-5 keywords')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                        $livewire->emit('seoDataUpdated',
                            $livewire->data['meta_title'] ?? '',
                            $livewire->data['meta_description'] ?? '',
                            $state,
                            $livewire->data['canonical_url'] ?? ''
                        );
                    }),

                TextInput::make('canonical_url')
                    ->label('Canonical URL')
                    ->placeholder('https://example.com/page')
                    ->helperText('Use this to prevent duplicate content issues')
                    ->url()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $livewire) {
                        $livewire->emit('seoDataUpdated',
                            $livewire->data['meta_title'] ?? '',
                            $livewire->data['meta_description'] ?? '',
                            $livewire->data['meta_keywords'] ?? '',
                            $state
                        );
                    }),

                View::make('filament.components.seo-analyzer'),
            ]);

        return $schema;
    }

    #[On('updatePreview')]
    public function updatePreview($content)
    {
        // This method will be called when the content is updated
        // You can perform any necessary transformations here
        $this->emit('updatePreview', $content);
        $this->emit('contentUpdated', $content, $this->data['title'] ?? '');
    }

    protected function afterSave(): void
    {
        // Create a new version after saving the content
        $this->record->createVersion();
    }
}