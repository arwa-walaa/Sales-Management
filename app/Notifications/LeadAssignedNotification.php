<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Lead $lead;

    /**
     * Create a new notification instance.
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Lead Assigned to You')
            ->greeting("Hello {$notifiable->name}!")
            ->line('A new lead has been assigned to you.')
            ->line("Lead Name: {$this->lead->name}")
            ->line("Phone: {$this->lead->phone}")
            ->line("Branch: {$this->lead->branch->name}")
            ->line("Status: {$this->lead->status}")
            ->action('View Lead', url("/api/leads/{$this->lead->id}"))
            ->line('Thank you for your dedication!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name,
            'lead_phone' => $this->lead->phone,
            'branch' => $this->lead->branch->name,
        ];
    }
}
