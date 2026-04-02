<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LoginAlertFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $ipAddress,
        public string $errorMessage,
        public string $attemptedAt,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Echec envoi alerte de connexion',
            'user_name' => $this->userName,
            'user_email' => $this->userEmail,
            'ip_address' => $this->ipAddress,
            'error_message' => $this->errorMessage,
            'attempted_at' => $this->attemptedAt,
        ];
    }
}
