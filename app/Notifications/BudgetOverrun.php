<?php

namespace App\Notifications;

use App\Models\ProjectBudgetItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetOverrun extends Notification
{
    use Queueable;

    public function __construct(private readonly ProjectBudgetItem $item)
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
            ->subject('Dépassement budgétaire')
            ->line($this->item->name)
            ->action('Voir le budget', url('/budgets'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->item->id,
            'name' => $this->item->name,
            'project_id' => $this->item->project_id,
            'estimated_cost' => $this->item->estimated_cost,
            'actual_cost' => $this->item->actual_cost,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'budget_item' => $this->toArray($notifiable),
        ]);
    }
}

