<?php

namespace App\Livewire\System;

use App\Models\AppSetting;
use App\Notifications\LoginAlertFailedNotification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Health extends Component
{
    public function render(): View
    {
        $user = auth()->user();

        abort_unless($user?->isAdmin(), 403);

        $loginAlertHealth = [
            'enabled' => AppSetting::boolean('login_alert_enabled'),
            'mailer' => config('mail.default'),
            'host' => (string) (config('mail.mailers.'.config('mail.default').'.host') ?? '—'),
            'from_address' => (string) (config('mail.from.address') ?? '—'),
            'company_email' => AppSetting::string('company_email'),
            'dedicated_recipient' => AppSetting::string('login_alert_recipient'),
            'effective_recipients' => AppSetting::loginAlertRecipients(),
            'last_status' => AppSetting::string('login_alert_last_status'),
            'last_error' => AppSetting::string('login_alert_last_error'),
            'last_attempt_at' => AppSetting::string('login_alert_last_attempt_at'),
        ];

        $internalLoginAlerts = $user->notifications()
            ->where('type', LoginAlertFailedNotification::class)
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.system.health', [
            'loginAlertHealth' => $loginAlertHealth,
            'internalLoginAlerts' => $internalLoginAlerts,
        ])->layout('layouts.app');
    }
}
