<?php

namespace App\Notifications;

use App\Models\Decision;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DecisionLogged extends Notification
{
    use Queueable;

    public function __construct(private readonly Decision $decision)
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
            ->subject('Decision logged')
            ->line($this->decision->title)
            ->action('View decisions', url('/decisions'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->decision->id,
            'title' => $this->decision->title,
            'project_id' => $this->decision->project_id,
            'decided_at' => $this->decision->decided_at,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'decision' => $this->toArray($notifiable),
        ]);
    }
}

