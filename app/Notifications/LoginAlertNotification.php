<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $userRole,
        public string $loggedInAt,
        public string $ipAddress,
        public string $userAgent,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
            ->subject('Alerte de connexion reussie')
            ->greeting('Connexion detectee')
            ->line("Utilisateur : {$this->userName}")
            ->line("Email : {$this->userEmail}")
            ->line("Role : {$this->userRole}")
            ->line("Date : {$this->loggedInAt}")
            ->line("Adresse IP : {$this->ipAddress}")
            ->line("Navigateur / appareil : {$this->userAgent}")
            ->line("Cette notification confirme une connexion reussie a l'application.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_name' => $this->userName,
            'user_email' => $this->userEmail,
            'user_role' => $this->userRole,
            'logged_in_at' => $this->loggedInAt,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}
