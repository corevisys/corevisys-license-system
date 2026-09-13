<?php

namespace App\Jobs;

use App\Models\License;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendExpiryNotification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $license;
    protected $days;

    public function __construct(License $license, int $days = 0)
    {
        $this->license = $license;
        $this->days = $days;
    }

    public function handle(): void
    {
        $user = $this->license->user;
        $prefs = $user->notificationPreference;

        if (!$prefs)
            return;

        $timeFrame = $this->days > 0 ? "in {$this->days} days" : "soon";

        if ($prefs->notify_via_email) {
            Log::info("Sending Expiry Email to {$user->email} for license {$this->license->id}. Expires {$timeFrame}.");
        }

        if ($prefs->notify_via_sms && $prefs->phone_number) {
            Log::info("Sending Expiry SMS to {$prefs->phone_number} for license {$this->license->id}. Expires {$timeFrame}.");
        }

        if ($prefs->notify_via_push && $prefs->fcm_token) {
            Log::info("Sending Expiry Push Notification to {$prefs->fcm_token} for license {$this->license->id}. Expires {$timeFrame}.");
        }
    }
}
