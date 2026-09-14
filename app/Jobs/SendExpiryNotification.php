<?php

namespace App\Jobs;

use App\Models\License;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            try {
                Mail::raw(
                    "Your CoreVisys license expires {$timeFrame}. License ID: {$this->license->id}.",
                    function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('CoreVisys License Expiry Notice');
                    }
                );
            } catch (\Throwable $e) {
                Log::error('Expiry email delivery failed', [
                    'license_id' => $this->license->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        }

        if ($prefs->notify_via_sms && $prefs->phone_number) {
            Log::info("Sending Expiry SMS to {$prefs->phone_number} for license {$this->license->id}. Expires {$timeFrame}.");
        }

        if ($prefs->notify_via_push && $prefs->fcm_token) {
            Log::info("Sending Expiry Push Notification to {$prefs->fcm_token} for license {$this->license->id}. Expires {$timeFrame}.");
        }
    }
}
