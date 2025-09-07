<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishScheduledContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contentId;

    public function __construct($contentId)
    {
        $this->contentId = $contentId;
    }

    public function handle()
    {
        try {
            $content = Content::find($this->contentId);

            if (!$content) {
                Log::warning("Scheduled content not found: {$this->contentId}");
                return;
            }

            if ($content->workflow_status !== Content::STATUS_SCHEDULED) {
                Log::info("Content {$this->contentId} is no longer scheduled for publishing");
                return;
            }

            if ($content->scheduled_for && $content->scheduled_for->isPast()) {
                $content->publish();
                Log::info("Successfully published scheduled content: {$content->title} (ID: {$content->id})");
            }

        } catch (\Exception $e) {
            Log::error("Failed to publish scheduled content {$this->contentId}: " . $e->getMessage());
            throw $e;
        }
    }
}