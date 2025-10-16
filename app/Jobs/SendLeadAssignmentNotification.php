<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Notifications\LeadAssignedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLeadAssignmentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Lead $lead;

    /**
     * Create a new job instance.
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->lead->user) {
                $this->lead->user->notify(new LeadAssignedNotification($this->lead));
            } 
        } catch (\Throwable $e) {
            Log::error('Error while sending LeadAssignedNotification', [
                'lead_id' => $this->lead->id,
                'error' => $e->getMessage(),
            ]);
            throw $e; // rethrow so the job can be retried
        }
    }
}
