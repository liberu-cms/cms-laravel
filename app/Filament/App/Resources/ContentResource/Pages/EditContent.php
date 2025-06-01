<?php

namespace App\Filament\App\Resources\ContentResource\Pages;

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
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->can('delete', $this->record)),
            Actions\Action::make('version_history')
                ->url(fn () => $this->getResource()::getUrl('version-history', ['record' => $this->record]))
                ->icon('heroicon-o-clock')
                ->label('Version History'),
        ];

        // Add workflow actions based on current status and user permissions
        if ($this->record->isDraft()) {
            if (auth()->user()->can('review_content')) {
                $actions[] = Actions\Action::make('submit_for_review')
                    ->label('Submit for Review')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        Forms\Components\Select::make('review_by')
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
        }

        if ($this->record->isInReview() && auth()->user()->can('review', $this->record)) {
            $actions[] = Actions\Action::make('approve')
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

            $actions[] = Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
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

        if ($this->record->isApproved() && auth()->user()->can('publish', $this->record)) {
            $actions[] = Actions\Action::make('publish')
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

            $actions[] = Actions\Action::make('schedule')
                ->label('Schedule')
                ->icon('heroicon-o-calendar')
                ->form([
                    Forms\Components\DateTimePicker::make('scheduled_for')
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

    #[On('updatePreview')]
    public function updatePreview($content)
    {
        // This method will be called when the content is updated
        // You can perform any necessary transformations here
        $this->emit('updatePreview', $content);
    }

    protected function afterSave(): void
    {
        // Create a new version after saving the content
        $this->record->createVersion();
    }
}