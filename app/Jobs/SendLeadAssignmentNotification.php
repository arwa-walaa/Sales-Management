<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Notifications\LeadAssignedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        if ($this->lead->user) {
            $this->lead->user->notify(new LeadAssignedNotification($this->lead));
        }
    }
}
