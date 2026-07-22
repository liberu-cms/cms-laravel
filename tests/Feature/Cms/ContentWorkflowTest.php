<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Liberu\Cms\Content\Workflow\InvalidWorkflowTransition;
use Liberu\Cms\Contracts\Content\WorkflowInterface;
use Liberu\Cms\Contracts\Content\WorkflowState;
use Liberu\Cms\Contracts\Events\Content\ContentPublished;
use Liberu\Cms\Contracts\Events\Content\ContentStateChanged;
use Tests\Fixtures\WorkflowContent;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Schema::create('workflow_contents', function (Blueprint $table): void {
        $table->id();
        $table->string('title')->nullable();
        $table->string('status')->default('draft');
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });
});

it('starts content in the draft state', function (): void {
    $item = WorkflowContent::create(['title' => 'Hello']);

    expect($item->workflowState())->toBe(WorkflowState::Draft)
        ->and($item->isPublished())->toBeFalse();
});

it('moves through the editorial lifecycle', function (): void {
    $item = WorkflowContent::create(['title' => 'Hello']);

    $item->submitForReview();
    expect($item->fresh()->workflowState())->toBe(WorkflowState::Review);

    $item->publish();
    expect($item->fresh()->isPublished())->toBeTrue()
        ->and($item->fresh()->isLive())->toBeTrue();

    $item->archive();
    expect($item->fresh()->workflowState())->toBe(WorkflowState::Archived);
});

it('rejects an illegal transition', function (): void {
    $item = WorkflowContent::create(['title' => 'Hello']);
    $item->publish();

    expect(fn () => $item->submitForReview())
        ->toThrow(InvalidWorkflowTransition::class);
});

it('treats future-dated publishing as scheduled but not live', function (): void {
    $item = WorkflowContent::create(['title' => 'Hello']);

    $item->schedule(now()->addDay());
    $item = $item->fresh();

    expect($item->isPublished())->toBeTrue()
        ->and($item->isLive())->toBeFalse();
});

it('broadcasts state-change and publish events', function (): void {
    Event::fake([ContentPublished::class, ContentStateChanged::class]);

    $item = WorkflowContent::create(['title' => 'Hello']);
    $item->publish();

    Event::assertDispatched(ContentStateChanged::class, fn (ContentStateChanged $e): bool => $e->to === WorkflowState::Published && $e->contentType === 'workflow-content');
    Event::assertDispatched(ContentPublished::class);
});

it('records numbered revisions and reverts to them', function (): void {
    $item = WorkflowContent::create(['title' => 'version one']);
    $item->recordRevision();

    $item->update(['title' => 'version two']);
    $item->recordRevision();

    expect($item->revisions()->count())->toBe(2)
        ->and($item->latestRevisionNumber())->toBe(2);

    expect($item->revertTo(1))->toBeTrue()
        ->and($item->fresh()->title)->toBe('version one');
});

it('returns false when reverting to a missing revision', function (): void {
    $item = WorkflowContent::create(['title' => 'only']);

    expect($item->revertTo(99))->toBeFalse();
});

it('resolves the workflow service from the container', function (): void {
    expect(app(WorkflowInterface::class)->canTransition(WorkflowState::Draft, WorkflowState::Published))->toBeTrue()
        ->and(app(WorkflowInterface::class)->canTransition(WorkflowState::Archived, WorkflowState::Published))->toBeFalse();
});
