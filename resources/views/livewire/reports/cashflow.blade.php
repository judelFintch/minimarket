<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Solde entree / sortie</h2>
            <p class="app-subtitle">Flux de tresorerie par periode et devise.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Filtres</h3>
                    <p class="app-card-subtitle">Choisissez la periode.</p>
                </div>
            </div>
            <div class="app-card-body">
                <div class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label class="app-label">Date debut</label>
                        <input type="date" wire:model.live="startDate" class="app-input" />
                    </div>
                    <div>
                        <label class="app-label">Date fin</label>
                        <input type="date" wire:model.live="endDate" class="app-input" />
                    </div>
                    <div>
                        <label class="app-label">Devise</label>
                        <select wire:model.live="currency" class="app-select">
                            <option value="">Toutes</option>
                            <option value="CDF">CDF</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Resume</h3>
                    <p class="app-card-subtitle">Entrees, sorties et solde net.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Devise</th>
                            <th>Entrees</th>
                            <th>Sorties</th>
                            <th>Solde</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($summary as $row)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $row['currency'] }}</td>
                                <td>{{ number_format($row['income'], 2) }}</td>
                                <td>{{ number_format($row['expense'], 2) }}</td>
                                <td class="font-semibold {{ $row['balance'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Aucune donnee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
