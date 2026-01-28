<?php

namespace App\Notifications;

use App\Models\Validation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ValidationRequested extends Notification
{
    use Queueable;

    public function __construct(private readonly Validation $validation)
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
            ->subject('Validation request pending')
            ->line($this->validation->title)
            ->line('Type: '.$this->validation->type)
            ->action('View validation', url('/validations'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->validation->id,
            'title' => $this->validation->title,
            'type' => $this->validation->type,
            'status' => $this->validation->status,
            'project_id' => $this->validation->project_id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'validation' => $this->toArray($notifiable),
        ]);
    }
}

