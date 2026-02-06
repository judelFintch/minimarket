<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Rapport ventes</h2>
            <p class="app-subtitle">Synthese des ventes avec benefice.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Filtres</h3>
                    <p class="app-card-subtitle">Choisissez la periode.</p>
                </div>
            </div>
            <div class="app-card-body">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="app-label">Du</label>
                        <input type="date" wire:model.live="startDate" class="app-input" />
                    </div>
                    <div>
                        <label class="app-label">Au</label>
                        <input type="date" wire:model.live="endDate" class="app-input" />
                    </div>
                    <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-400">Ventes</div>
                        <div class="text-2xl font-semibold text-slate-900">{{ number_format($salesCount) }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ number_format($itemsCount) }} articles</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @forelse ($summaryByCurrency as $summary)
                <div class="app-kpi">
                    <div class="app-kpi-label">Chiffre d'affaires ({{ $summary->currency }})</div>
                    <div class="app-kpi-value">{{ number_format((float) $summary->revenue, 2) }}</div>
                    <div class="mt-3 text-xs text-slate-500">Cout: {{ number_format((float) $summary->cost, 2) }}</div>
                    <div class="mt-1 text-sm font-semibold text-emerald-600">
                        Benefice: {{ number_format((float) $summary->profit, 2) }}
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center text-sm text-slate-500 md:col-span-3">
                    Aucun resultat pour cette periode.
                </div>
            @endforelse
        </div>
    </div>
</div>
