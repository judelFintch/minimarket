<div class="space-y-8">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="app-title">Sante systeme</h2>
                <p class="app-subtitle">Diagnostic des alertes email et incidents internes.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('system.index') }}" wire:navigate class="app-btn-secondary">Systeme</a>
                <a href="{{ route('dashboard') }}" wire:navigate class="app-btn-ghost">Retour dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Sante systeme email</h3>
                    <p class="app-card-subtitle">Diagnostic du canal d'alerte de connexion.</p>
                </div>
            </div>
            <div class="app-card-body space-y-3 text-sm text-slate-600">
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-slate-900">Etat</span>
                    <span class="app-badge {{ $loginAlertHealth['enabled'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                        {{ $loginAlertHealth['enabled'] ? 'Actif' : 'Desactive' }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-slate-900">Mailer</span>
                    <span>{{ $loginAlertHealth['mailer'] ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-slate-900">Host SMTP</span>
                    <span>{{ $loginAlertHealth['host'] ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-slate-900">Expediteur</span>
                    <span>{{ $loginAlertHealth['from_address'] ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-slate-900">Email entreprise</span>
                    <span>{{ $loginAlertHealth['company_email'] ?: '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-slate-900">Email dedie</span>
                    <span>{{ $loginAlertHealth['dedicated_recipient'] ?: '—' }}</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="font-semibold text-slate-900">Destinataires effectifs</span>
                    <span class="text-right">
                        {{ $loginAlertHealth['effective_recipients'] !== [] ? implode(', ', $loginAlertHealth['effective_recipients']) : 'Aucun' }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-slate-900">Dernier envoi</span>
                    <span>{{ $loginAlertHealth['last_status'] ?: 'Jamais tente' }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span class="font-semibold text-slate-900">Derniere tentative</span>
                    <span>{{ $loginAlertHealth['last_attempt_at'] ?: '—' }}</span>
                </div>
                <div class="rounded-xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Derniere erreur</div>
                    <div class="mt-1 text-sm text-slate-700">{{ $loginAlertHealth['last_error'] ?: 'Aucune erreur enregistree.' }}</div>
                </div>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Alertes internes email</h3>
                    <p class="app-card-subtitle">Derniers echecs de notification de connexion.</p>
                </div>
            </div>
            <div class="app-card-body space-y-3 text-sm text-slate-600">
                @forelse ($internalLoginAlerts as $alert)
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
                        <div class="font-semibold text-rose-700">{{ $alert->data['title'] ?? 'Alerte' }}</div>
                        <div class="mt-1 text-slate-700">{{ $alert->data['user_name'] ?? 'Utilisateur' }} ({{ $alert->data['user_email'] ?? '—' }})</div>
                        <div class="mt-1 text-xs text-slate-500">IP: {{ $alert->data['ip_address'] ?? '—' }} | {{ $alert->data['attempted_at'] ?? '—' }}</div>
                        <div class="mt-2 text-xs text-rose-700">{{ $alert->data['error_message'] ?? 'Erreur inconnue.' }}</div>
                    </div>
                @empty
                    <div class="text-sm text-slate-500">Aucun echec d'envoi enregistre.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
