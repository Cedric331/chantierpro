<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentReported extends Notification
{
    use Queueable;

    public function __construct(private readonly Incident $incident)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        if (class_exists(\Pusher\Pusher::class)) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Incident reported')
            ->line($this->incident->title)
            ->line('Impact: '.$this->incident->impact_days.' day(s)')
            ->action('View incidents', url('/incidents'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->incident->id,
            'title' => $this->incident->title,
            'status' => $this->incident->status,
            'project_id' => $this->incident->project_id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'incident' => $this->toArray($notifiable),
        ]);
    }
}

