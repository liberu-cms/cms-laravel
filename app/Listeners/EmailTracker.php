<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\EmailCampaign;
use App\Models\Subscriber;
use App\Models\EmailStat;

class EmailTracker
{
    public function handle(MessageSent $event)
    {
        $message = $event->message;
        $headers = $message->getHeaders();

        $campaignId = $headers->get('X-Campaign-ID');
        $subscriberId = $headers->get('X-Subscriber-ID');

        if ($campaignId && $subscriberId) {
            $campaign = EmailCampaign::find($campaignId);
            $subscriber = Subscriber::find($subscriberId);

            if ($campaign && $subscriber) {
                // Record that the email was sent
                EmailStat::updateOrCreate(
                    [
                        'email_campaign_id' => $campaign->id,
                        'subscriber_id' => $subscriber->id,
                    ],
                    [
                        'sent_at' => now(),
                    ]
                );
            }
        }
    }
}