<?php

namespace App\Livewire\System;

use App\Models\AppSetting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public bool $loginAlertEnabled = false;

    public string $loginAlertRecipient = '';

    public string $companyEmail = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $this->loginAlertEnabled = AppSetting::boolean('login_alert_enabled');
        $this->loginAlertRecipient = AppSetting::string('login_alert_recipient', '') ?? '';
        $this->companyEmail = AppSetting::string('company_email', '') ?? '';
    }

    protected function rules(): array
    {
        return [
            'loginAlertEnabled' => ['required', 'boolean'],
            'loginAlertRecipient' => ['nullable', 'email', 'max:255'],
            'companyEmail' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function saveLoginAlertSettings(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $validated = $this->validate();

        if ($validated['loginAlertEnabled'] && blank($validated['loginAlertRecipient']) && blank($validated['companyEmail'])) {
            $message = "Configurez au moins un destinataire email avant d'activer les alertes de connexion.";
            $this->addError('loginAlertRecipient', $message);
            $this->addError('companyEmail', $message);

            return;
        }

        AppSetting::set('login_alert_enabled', $validated['loginAlertEnabled'] ? '1' : '0');
        AppSetting::set('login_alert_recipient', filled($validated['loginAlertRecipient']) ? trim($validated['loginAlertRecipient']) : null);
        AppSetting::set('company_email', filled($validated['companyEmail']) ? trim($validated['companyEmail']) : null);

        session()->flash('login-alert-settings-saved', 'Configuration des alertes de connexion enregistree.');
    }

    public function render(): View
    {
        return view('livewire.system.index')->layout('layouts.app');
    }
}
