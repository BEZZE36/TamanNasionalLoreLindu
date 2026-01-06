<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public NewsletterCampaign $campaign;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(NewsletterCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh campaign to get latest data
        $this->campaign->refresh();

        // Get all active subscribers
        $subscribers = NewsletterSubscriber::where('is_active', true)->get();

        if ($subscribers->isEmpty()) {
            $this->campaign->markAsSent();
            Log::info("Newsletter campaign #{$this->campaign->id} completed - No subscribers.");
            return;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)
                    ->send(new NewsletterMail(
                        $this->campaign,
                        $subscriber->email,
                        $subscriber->name
                    ));

                $sentCount++;

                // Update campaign progress
                $this->campaign->update(['sent_count' => $sentCount]);

                // Small delay to avoid rate limiting
                usleep(100000); // 100ms delay

            } catch (\Exception $e) {
                $failedCount++;
                $this->campaign->update(['failed_count' => $failedCount]);
                Log::error("Failed to send newsletter to {$subscriber->email}: " . $e->getMessage());
            }
        }

        // Mark campaign as sent or failed
        if ($failedCount === $subscribers->count()) {
            $this->campaign->markAsFailed('All emails failed to send');
        } else {
            $this->campaign->markAsSent();
        }

        Log::info("Newsletter campaign #{$this->campaign->id} completed. Sent: {$sentCount}, Failed: {$failedCount}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->campaign->markAsFailed($exception->getMessage());
        Log::error("Newsletter campaign #{$this->campaign->id} failed: " . $exception->getMessage());
    }
}
