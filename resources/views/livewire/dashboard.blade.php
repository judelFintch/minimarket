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
                @if ($isAdmin)
                    <a href="{{ route('users.index') }}" wire:navigate class="app-btn-secondary">Utilisateurs</a>
                    <a href="{{ route('system.index') }}" wire:navigate class="app-btn-secondary">Systeme</a>
                    <a href="{{ route('system.health') }}" wire:navigate class="app-btn-ghost">Sante systeme</a>
                    <a href="{{ route('reports.sales') }}" wire:navigate class="app-btn-ghost">Rapport ventes</a>
                    <a href="{{ route('reports.cashflow') }}" wire:navigate class="app-btn-ghost">Rapport cashflow</a>
                    <a href="{{ route('stocks.alerts') }}" wire:navigate class="app-btn-ghost">Alertes stock</a>
                @endif
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

    @if ($isAdmin)
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="app-kpi">
                <div class="app-kpi-label">Utilisateurs</div>
                <div class="app-kpi-value">{{ $usersCount }}</div>
                <div class="app-kpi-trend">{{ $suspendedUsersCount }} suspendus</div>
            </div>
            <div class="app-kpi">
                <div class="app-kpi-label">Comptes non verifies</div>
                <div class="app-kpi-value">{{ $unverifiedUsersCount }}</div>
                <div class="app-kpi-trend">A valider</div>
            </div>
            <div class="app-kpi">
                <div class="app-kpi-label">Ventes impayees</div>
                <div class="app-kpi-value">{{ $unpaidSalesCount }}</div>
                <div class="app-kpi-trend">
                    @forelse ($unpaidSalesByCurrency as $row)
                        <div>{{ number_format($row->total, 2) }} {{ $row->currency }}</div>
                    @empty
                        0
                    @endforelse
                </div>
            </div>
            <div class="app-kpi">
                <div class="app-kpi-label">Ruptures de stock</div>
                <div class="app-kpi-value">{{ $outOfStockCount }}</div>
                <div class="app-kpi-trend">{{ $lowStockCount }} sous seuil</div>
            </div>
        </div>
    @endif

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
                    <h3 class="app-card-title">Priorites</h3>
                    <p class="app-card-subtitle">Ce qui demande une action rapide.</p>
                </div>
            </div>
            <div class="app-card-body space-y-4 text-sm text-slate-600">
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-amber-400"></span>
                    {{ $lowStockCount }} produits sous le seuil de stock.
                </div>
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-teal-400"></span>
                    <a href="{{ route('stocks.alerts') }}" wire:navigate class="font-semibold text-slate-900 hover:text-teal-700">Consultez les reapprovisionnements critiques.</a>
                </div>
                <div class="flex items-start gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full bg-sky-400"></span>
                    <a href="{{ route('reports.cashflow') }}" wire:navigate class="font-semibold text-slate-900 hover:text-teal-700">Suivez le solde net et les depenses mensuelles.</a>
                </div>
            </div>
        </div>
    </div>

    @if ($isAdmin)
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="app-card">
                <div class="app-card-header">
                    <div>
                        <h3 class="app-card-title">Pilotage admin</h3>
                        <p class="app-card-subtitle">Resume compact des points a surveiller.</p>
                    </div>
                </div>
                <div class="app-card-body">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Marge brute</div>
                            <div class="mt-2 space-y-1 text-sm text-slate-700">
                                @forelse ($marginByCurrency as $row)
                                    <div class="flex items-center justify-between">
                                        <span>{{ $row->currency }}</span>
                                        <span class="font-semibold {{ $row->margin >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($row->margin, 2) }}</span>
                                    </div>
                                @empty
                                    <div>Aucune donnee.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Qualite des donnees</div>
                            <div class="mt-2 space-y-1 text-sm text-slate-700">
                                <div>{{ $missingSalePriceCount }} sans prix de vente</div>
                                <div>{{ $missingCostPriceCount }} sans prix d'achat</div>
                                <div>{{ $missingMinStockCount }} sans seuil mini</div>
                                <div>{{ $inactiveSuppliersCount }} fournisseurs inactifs</div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Stock</div>
                            <div class="mt-2 space-y-1 text-sm text-slate-700">
                                <div>{{ $outOfStockCount }} produits en rupture</div>
                                <div>{{ $slowMovingCount }} produits lents</div>
                                @forelse ($stockValueByCurrency as $row)
                                    <div class="flex items-center justify-between">
                                        <span>Valeur {{ $row->currency }}</span>
                                        <span class="font-semibold">{{ number_format($row->total, 2) }}</span>
                                    </div>
                                @empty
                                    <div>Aucune valorisation.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                            <div class="text-xs uppercase tracking-wider text-slate-500">Equipe</div>
                            <div class="mt-2 space-y-1 text-sm text-slate-700">
                                <div>{{ $usersCount }} utilisateurs</div>
                                <div>{{ $suspendedUsersCount }} suspendus</div>
                                <div>{{ $unverifiedUsersCount }} non verifies</div>
                                <div>{{ $unpaidSalesCount }} ventes impayees</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card-header">
                    <div>
                        <h3 class="app-card-title">Activite recente</h3>
                        <p class="app-card-subtitle">Ce qu'il faut surveiller sans descendre toute la page.</p>
                    </div>
                </div>
                <div class="app-card-body space-y-5">
                    <div>
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Top vendeurs</div>
                        <div class="space-y-2 text-sm text-slate-700">
                            @forelse ($salesByUser as $row)
                                <div class="flex items-center justify-between rounded-xl border border-slate-200/70 bg-slate-50 px-3 py-2">
                                    <span class="font-semibold text-slate-900">{{ $row->user_name }}</span>
                                    <span>{{ $row->sales_count }} ventes</span>
                                </div>
                            @empty
                                <div class="text-slate-500">Aucune vente.</div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Utilisateurs recents</div>
                        <div class="space-y-2 text-sm text-slate-700">
                            @forelse ($recentUsers as $row)
                                <div class="flex items-center justify-between rounded-xl border border-slate-200/70 bg-slate-50 px-3 py-2">
                                    <span class="font-semibold text-slate-900">{{ $row->name }}</span>
                                    <span>{{ $row->role ?? 'vendeur' }}</span>
                                </div>
                            @empty
                                <div class="text-slate-500">Aucun utilisateur.</div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Fournisseurs inactifs</div>
                        <div class="space-y-2 text-sm text-slate-700">
                            @forelse ($inactiveSuppliers as $row)
                                <div class="flex items-center justify-between rounded-xl border border-slate-200/70 bg-slate-50 px-3 py-2">
                                    <span class="font-semibold text-slate-900">{{ $row->name }}</span>
                                    <span>{{ $row->last_purchase_at ? \Illuminate\Support\Carbon::parse($row->last_purchase_at)->format('Y-m-d') : 'Jamais' }}</span>
                                </div>
                            @empty
                                <div class="text-slate-500">Aucun fournisseur inactif.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
