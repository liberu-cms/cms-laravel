<?php

namespace App\Services;

use App\Models\Content;
use App\Jobs\PublishScheduledContentJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContentAutomationService
{
    public function scheduleContentPublication(Content $content, Carbon $publishDate)
    {
        // Update content status and schedule date
        $content->update([
            'workflow_status' => Content::STATUS_SCHEDULED,
            'scheduled_for' => $publishDate,
        ]);

        // Schedule the job to publish the content
        PublishScheduledContentJob::dispatch($content->id)->delay($publishDate);

        Log::info("Scheduled content for publication: {$content->title} at {$publishDate}");

        return $content;
    }

    public function cancelScheduledPublication(Content $content)
    {
        $content->update([
            'workflow_status' => Content::STATUS_DRAFT,
            'scheduled_for' => null,
        ]);

        // Note: Laravel doesn't provide a built-in way to cancel delayed jobs
        // You might need to implement a custom solution or use a package like laravel-horizon

        Log::info("Cancelled scheduled publication for: {$content->title}");

        return $content;
    }

    public function getScheduledContent($startDate = null, $endDate = null)
    {
        $query = Content::where('workflow_status', Content::STATUS_SCHEDULED);

        if ($startDate) {
            $query->where('scheduled_for', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('scheduled_for', '<=', $endDate);
        }

        return $query->orderBy('scheduled_for')->get();
    }

    public function getUpcomingPublications($days = 7)
    {
        $endDate = now()->addDays($days);

        return $this->getScheduledContent(now(), $endDate);
    }

    public function getOverdueScheduledContent()
    {
        return Content::where('workflow_status', Content::STATUS_SCHEDULED)
            ->where('scheduled_for', '<', now())
            ->orderBy('scheduled_for')
            ->get();
    }

    public function processOverdueContent()
    {
        $overdueContent = $this->getOverdueScheduledContent();
        $processedCount = 0;

        foreach ($overdueContent as $content) {
            try {
                $content->publish();
                $processedCount++;
                Log::info("Published overdue content: {$content->title}");
            } catch (\Exception $e) {
                Log::error("Failed to publish overdue content {$content->id}: " . $e->getMessage());
            }
        }

        return $processedCount;
    }

    public function bulkScheduleContent(array $contentIds, Carbon $publishDate)
    {
        $scheduled = [];
        $errors = [];

        foreach ($contentIds as $contentId) {
            try {
                $content = Content::findOrFail($contentId);
                $this->scheduleContentPublication($content, $publishDate);
                $scheduled[] = $content;
            } catch (\Exception $e) {
                $errors[] = [
                    'content_id' => $contentId,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'scheduled' => $scheduled,
            'errors' => $errors,
            'success_count' => count($scheduled),
            'error_count' => count($errors)
        ];
    }

    public function rescheduleContent(Content $content, Carbon $newPublishDate)
    {
        // Cancel current schedule
        $this->cancelScheduledPublication($content);

        // Schedule for new date
        return $this->scheduleContentPublication($content, $newPublishDate);
    }

    public function getContentScheduleStats()
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();
        $thisWeek = now()->endOfWeek();
        $thisMonth = now()->endOfMonth();

        return [
            'total_scheduled' => Content::where('workflow_status', Content::STATUS_SCHEDULED)->count(),
            'scheduled_today' => Content::where('workflow_status', Content::STATUS_SCHEDULED)
                ->whereBetween('scheduled_for', [$today, $tomorrow])
                ->count(),
            'scheduled_this_week' => Content::where('workflow_status', Content::STATUS_SCHEDULED)
                ->whereBetween('scheduled_for', [now(), $thisWeek])
                ->count(),
            'scheduled_this_month' => Content::where('workflow_status', Content::STATUS_SCHEDULED)
                ->whereBetween('scheduled_for', [now(), $thisMonth])
                ->count(),
            'overdue' => $this->getOverdueScheduledContent()->count(),
        ];
    }

    public function autoOptimizePublishTimes()
    {
        // This method could analyze historical performance data
        // to suggest optimal publish times for different content types

        $analytics = app(ContentAnalyticsService::class);

        // Get best performing publish times from analytics
        $optimalTimes = $analytics->getBestPublishTimes();

        return $optimalTimes;
    }

    public function createContentSeries($baseContent, $seriesConfig)
    {
        $series = [];
        $publishDate = Carbon::parse($seriesConfig['start_date']);

        for ($i = 0; $i < $seriesConfig['count']; $i++) {
            $content = $baseContent->replicate();
            $content->title = $seriesConfig['title_template'] . ' - Part ' . ($i + 1);
            $content->slug = $content->generateUniqueSlug($content->title);
            $content->workflow_status = Content::STATUS_SCHEDULED;
            $content->scheduled_for = $publishDate->copy();
            $content->save();

            $series[] = $content;

            // Increment publish date based on frequency
            switch ($seriesConfig['frequency']) {
                case 'daily':
                    $publishDate->addDay();
                    break;
                case 'weekly':
                    $publishDate->addWeek();
                    break;
                case 'monthly':
                    $publishDate->addMonth();
                    break;
            }
        }

        return $series;
    }

    public function setupRecurringContent($templateContent, $recurringConfig)
    {
        // This would set up recurring content publication
        // For example, weekly newsletters, monthly reports, etc.

        $nextPublishDate = Carbon::parse($recurringConfig['next_date']);
        $endDate = isset($recurringConfig['end_date']) ? Carbon::parse($recurringConfig['end_date']) : null;

        $recurringContent = [];

        while (!$endDate || $nextPublishDate->lte($endDate)) {
            $content = $templateContent->replicate();
            $content->title = $this->processRecurringTitle($recurringConfig['title_template'], $nextPublishDate);
            $content->slug = $content->generateUniqueSlug($content->title);
            $content->body = $this->processRecurringContent($content->body, $nextPublishDate);
            $content->workflow_status = Content::STATUS_SCHEDULED;
            $content->scheduled_for = $nextPublishDate->copy();
            $content->save();

            $recurringContent[] = $content;

            // Calculate next publish date
            switch ($recurringConfig['frequency']) {
                case 'daily':
                    $nextPublishDate->addDay();
                    break;
                case 'weekly':
                    $nextPublishDate->addWeek();
                    break;
                case 'monthly':
                    $nextPublishDate->addMonth();
                    break;
                case 'yearly':
                    $nextPublishDate->addYear();
                    break;
            }

            // Safety check to prevent infinite loops
            if (count($recurringContent) >= 100) {
                break;
            }
        }

        return $recurringContent;
    }

    protected function processRecurringTitle($template, Carbon $date)
    {
        $replacements = [
            '{date}' => $date->format('Y-m-d'),
            '{month}' => $date->format('F'),
            '{year}' => $date->format('Y'),
            '{week}' => $date->format('W'),
            '{day}' => $date->format('l'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function processRecurringContent($content, Carbon $date)
    {
        $replacements = [
            '{date}' => $date->format('Y-m-d'),
            '{month}' => $date->format('F'),
            '{year}' => $date->format('Y'),
            '{week}' => $date->format('W'),
            '{day}' => $date->format('l'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}