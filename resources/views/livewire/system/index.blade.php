<div class="space-y-8">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="app-title">Systeme</h2>
                <p class="app-subtitle">Configuration generale et alertes de connexion.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('system.health') }}" wire:navigate class="app-btn-secondary">Sante systeme</a>
                <a href="{{ route('dashboard') }}" wire:navigate class="app-btn-ghost">Retour dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Alertes de connexion</h3>
                    <p class="app-card-subtitle">Definissez les destinataires email pour chaque connexion reussie.</p>
                </div>
            </div>
            <div class="app-card-body">
                <form wire:submit.prevent="saveLoginAlertSettings" class="grid gap-4">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" wire:model.live="loginAlertEnabled" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                        Activer les alertes de connexion
                    </label>

                    <div>
                        <label class="app-label">Email d'alerte dedie</label>
                        <input type="email" wire:model.live="loginAlertRecipient" class="app-input" placeholder="alerte@example.com" />
                        @error('loginAlertRecipient') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Email principal entreprise</label>
                        <input type="email" wire:model.live="companyEmail" class="app-input" placeholder="contact@example.com" />
                        @error('companyEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="app-btn-primary">Enregistrer</button>
                        @if (session('login-alert-settings-saved'))
                            <span class="text-sm text-emerald-600">{{ session('login-alert-settings-saved') }}</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Organisation</h3>
                    <p class="app-card-subtitle">Acces rapide aux ecrans techniques.</p>
                </div>
            </div>
            <div class="app-card-body space-y-4 text-sm text-slate-600">
                <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Configuration</div>
                    <div class="mt-2 text-slate-700">Les parametres ont ete retires du dashboard et centralises ici.</div>
                </div>
                <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Diagnostic</div>
                    <div class="mt-2 text-slate-700">Consultez l'etat du canal email et les echecs d'envoi dans Sante systeme.</div>
                </div>
                <a href="{{ route('system.health') }}" wire:navigate class="app-btn-secondary w-full justify-center">Ouvrir Sante systeme</a>
            </div>
        </div>
    </div>
</div>
