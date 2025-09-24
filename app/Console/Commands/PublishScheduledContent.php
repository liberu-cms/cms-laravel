<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Content;
use App\Jobs\PublishScheduledContentJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledContent extends Command
{
    protected $signature = 'content:publish-scheduled';
    protected $description = 'Publish content that is scheduled for publication';

    public function handle()
    {
        $this->info('Checking for scheduled content to publish...');

        $scheduledContent = Content::where('workflow_status', Content::STATUS_SCHEDULED)
            ->where('scheduled_for', '<=', now())
            ->get();

        if ($scheduledContent->isEmpty()) {
            $this->info('No scheduled content found for publication.');
            return 0;
        }

        $publishedCount = 0;
        $errorCount = 0;

        foreach ($scheduledContent as $content) {
            try {
                $content->publish();
                $publishedCount++;
                $this->info("Published: {$content->title}");
                Log::info("Auto-published scheduled content: {$content->title} (ID: {$content->id})");
            } catch (Exception $e) {
                $errorCount++;
                $this->error("Failed to publish: {$content->title} - {$e->getMessage()}");
                Log::error("Failed to auto-publish content {$content->id}: " . $e->getMessage());
            }
        }

        $this->info("Publication complete. Published: {$publishedCount}, Errors: {$errorCount}");

        return 0;
    }
}