<div class="space-y-8">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="app-title">Tableau de bord</h2>
                <p class="app-subtitle">Vue d'ensemble des ventes, stocks et depenses.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('sales.index') }}" wire:navigate class="app-btn-primary">Nouvelle vente</a>
                <a href="{{ route('products.index') }}" wire:navigate class="app-btn-secondary">Ajouter produit</a>
                <a href="{{ route('stocks.index') }}" wire:navigate class="app-btn-secondary">Mouvement stock</a>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="app-kpi">
            <div class="app-kpi-label">Ventes du jour</div>
            <div class="app-kpi-value">{{ $salesTodayCount }}</div>
            <div class="app-kpi-trend">Transactions aujourd'hui</div>
        </div>
        <div class="app-kpi">
            <div class="app-kpi-label">Chiffre d'affaires (mois)</div>
            <div class="app-kpi-value">
                @forelse ($revenueByCurrency as $row)
                    <div>{{ number_format($row->total, 2) }} {{ $row->currency }}</div>
                @empty
                    0
                @endforelse
            </div>
            <div class="app-kpi-trend">Cumul mensuel</div>
        </div>
        <div class="app-kpi">
            <div class="app-kpi-label">Stock total</div>
            <div class="app-kpi-value">{{ number_format($stockCount, 2) }}</div>
            <div class="app-kpi-trend">{{ $lowStockCount }} produits sous seuil</div>
        </div>
        <div class="app-kpi">
            <div class="app-kpi-label">Fournisseurs actifs</div>
            <div class="app-kpi-value">{{ $suppliersCount }}</div>
            <div class="app-kpi-trend">Partenaires suivis</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="app-card lg:col-span-2">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Solde net (mois)</h3>
                    <p class="app-card-subtitle">Entrees - sorties par devise.</p>
                </div>
                <a href="{{ route('reports.cashflow') }}" wire:navigate class="app-btn-ghost">Voir le rapport</a>
            </div>
            <div class="app-card-body">
                <div class="grid gap-3 sm:grid-cols-2">
                    @forelse ($netByCurrency as $row)
                        <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-slate-500">{{ $row['currency'] }}</div>
                            <div class="mt-2 text-lg font-semibold {{ $row['net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ number_format($row['net'], 2) }}
                            </div>
                            <div class="mt-2 text-xs text-slate-500">+{{ number_format($row['income'], 2) }} / -{{ number_format($row['expense'], 2) }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">Aucune donnee ce mois.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Alertes rapides</h3>
                    <p class="app-card-subtitle">Actions prioritaires.</p>
                </div>
            </div>
            <div class="app-card-body space-y-4 text-sm text-slate-600">
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-amber-400"></span>
                    {{ $lowStockCount }} produits sous le seuil de stock.
                </div>
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-teal-400"></span>
                    Mettez a jour les reapprovisionnements critiques.
                </div>
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-sky-400"></span>
                    Suivi du solde net et des depenses mensuelles.
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Dernieres ventes</h3>
                    <p class="app-card-subtitle">Transactions recentes.</p>
                </div>
                <a href="{{ route('sales.history') }}" wire:navigate class="app-btn-ghost">Historique</a>
            </div>
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($recentSales as $sale)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $sale->reference }}</td>
                                <td>{{ $sale->sold_at?->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    <span class="app-badge {{ $sale->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $sale->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">Aucune vente recente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Dernieres depenses</h3>
                    <p class="app-card-subtitle">Dernieres sorties enregistrees.</p>
                </div>
                <a href="{{ route('expenses.index') }}" wire:navigate class="app-btn-ghost">Voir</a>
            </div>
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Depense</th>
                            <th>Date</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($recentExpenses as $expense)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $expense->title }}</td>
                                <td>{{ $expense->incurred_at }}</td>
                                <td>{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">Aucune depense recente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
