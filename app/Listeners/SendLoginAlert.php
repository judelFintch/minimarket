<?php

namespace App\Listeners;

use App\Models\AppSetting;
use App\Models\User;
use App\Notifications\LoginAlertFailedNotification;
use App\Notifications\LoginAlertNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendLoginAlert
{
    public function handle(Login $event): void
    {
        if (! AppSetting::boolean('login_alert_enabled')) {
            return;
        }

        $recipients = AppSetting::loginAlertRecipients();
        $attemptedAt = now();

        if ($recipients === []) {
            $this->recordFailure($event, 'Aucun destinataire configure pour les alertes de connexion.', $attemptedAt);

            return;
        }

        $request = request();
        $notification = new LoginAlertNotification(
            userName: $event->user->name,
            userEmail: $event->user->email,
            userRole: (string) ($event->user->role ?? 'vendeur'),
            loggedInAt: $attemptedAt->format('Y-m-d H:i:s'),
            ipAddress: $request->ip() ?? 'Inconnue',
            userAgent: substr((string) ($request->userAgent() ?? 'Inconnu'), 0, 500),
        );

        try {
            $this->sendMailAlert($recipients, $notification);

            AppSetting::set('login_alert_last_status', 'success');
            AppSetting::set('login_alert_last_error', null);
            AppSetting::set('login_alert_last_attempt_at', $attemptedAt->toDateTimeString());
        } catch (\Throwable $throwable) {
            $this->recordFailure($event, $throwable->getMessage(), $attemptedAt, $throwable);
        }
    }

    private function recordFailure(Login $event, string $errorMessage, \Illuminate\Support\Carbon $attemptedAt, ?\Throwable $throwable = null): void
    {
        AppSetting::set('login_alert_last_status', 'failed');
        AppSetting::set('login_alert_last_error', $errorMessage);
        AppSetting::set('login_alert_last_attempt_at', $attemptedAt->toDateTimeString());

        if ($throwable) {
            Log::error('Login alert email failed to send.', [
                'user_id' => $event->user->id,
                'user_email' => $event->user->email,
                'error' => $errorMessage,
            ]);
        } else {
            Log::warning('Login alert email skipped because no recipients are configured.', [
                'user_id' => $event->user->id,
                'user_email' => $event->user->email,
            ]);
        }

        $internalRecipients = User::query()
            ->whereIn('role', ['owner', 'manager', 'admin'])
            ->get();

        if ($internalRecipients->isEmpty()) {
            return;
        }

        $this->sendInternalFailureAlert($internalRecipients, new LoginAlertFailedNotification(
            userName: $event->user->name,
            userEmail: $event->user->email,
            ipAddress: request()->ip() ?? 'Inconnue',
            errorMessage: $errorMessage,
            attemptedAt: $attemptedAt->format('Y-m-d H:i:s'),
        ));
    }

    /**
     * @param  array<int, string>  $recipients
     */
    protected function sendMailAlert(array $recipients, LoginAlertNotification $notification): void
    {
        Notification::route('mail', $recipients)->notify($notification);
    }

    protected function sendInternalFailureAlert(\Illuminate\Support\Collection $internalRecipients, LoginAlertFailedNotification $notification): void
    {
        Notification::send($internalRecipients, $notification);
    }
}
