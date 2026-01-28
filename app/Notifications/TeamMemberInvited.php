<?php

namespace App\Notifications;

use App\Models\Account;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberInvited extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Account $account,
        private readonly User $inviter,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Invitation à rejoindre {$this->account->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("{$this->inviter->name} vous a invité à rejoindre l'équipe {$this->account->name}.")
            ->line("Un mot de passe temporaire a été créé pour vous. Pensez à le changer après connexion.")
            ->action('Se connecter', url('/login'))
            ->line("Si vous n'êtes pas concerné, vous pouvez ignorer cet e-mail.");
    }
}

