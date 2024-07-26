<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Content;
use Illuminate\Support\Facades\Http;

class ContentPublishLoadTest extends Command
{
    protected $signature = 'content:load-test {iterations=100}';
    protected $description = 'Perform a load test on content publishing';

    public function handle()
    {
        $iterations = $this->argument('iterations');
        $this->info("Starting load test with {$iterations} iterations...");

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $content = Content::factory()->create();
            $response = Http::post(route('content.publish', $content->id));
            
            if ($response->successful()) {
                $this->info("Iteration {$i}: Content published successfully");
            } else {
                $this->error("Iteration {$i}: Failed to publish content");
            }
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $avgTime = $totalTime / $iterations;

        $this->info("Load test completed.");
        $this->info("Total time: {$totalTime} seconds");
        $this->info("Average time per request: {$avgTime} seconds");
    }
}