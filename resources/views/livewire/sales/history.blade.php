<div class="space-y-8">
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="app-title">Historique des ventes</h2>
                <p class="app-subtitle">Suivi des ventes et factures PDF.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('sales.index') }}" wire:navigate class="app-btn-secondary">Retour a la vente</a>
                <a href="{{ route('dashboard') }}" wire:navigate class="app-btn-ghost">Dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Filtres</h3>
                    <p class="app-card-subtitle">Recherchez et filtrez par date ou statut.</p>
                </div>
                <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:w-48" />
                    <input type="date" wire:model.live="date_from" class="app-input sm:w-40" />
                    <input type="date" wire:model.live="date_to" class="app-input sm:w-40" />
                    <select wire:model.live="status_filter" class="app-select sm:w-40">
                        <option value="">Tous statuts</option>
                        <option value="paid">Payee</option>
                        <option value="pending">En attente</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Client</th>
                            <th>Articles</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Facture</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $sale->reference }}</td>
                                <td>{{ $sale->customer_name ?? 'Comptoir' }}</td>
                                <td>{{ $sale->items_count }}</td>
                                <td>{{ number_format($sale->total_amount, 2) }}</td>
                                <td>
                                    @if ($sale->status === 'paid')
                                        <span class="app-badge">Payee</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">En attente</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($sale->invoice)
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="app-badge">
                                                {{ ucfirst($sale->invoice->status) }}
                                            </span>
                                            <a href="{{ route('invoices.download', $sale->invoice) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-700">
                                                {{ $sale->invoice->invoice_number }}
                                            </a>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    {{ $sale->sold_at?->format('Y-m-d') ?? '—' }}
                                </td>
                                <td class="text-right">
                                    @if ($sale->status === 'pending')
                                        <button type="button" wire:click="finalizeSale({{ $sale->id }})" class="app-btn-ghost text-teal-600 hover:text-teal-700">
                                            Encaisser
                                        </button>
                                    @endif
                                    @if ($sale->invoice)
                                        <a href="{{ route('invoices.receipt', $sale->invoice) }}" class="app-btn-ghost text-emerald-600 hover:text-emerald-700">
                                            Ticket
                                        </a>
                                        <a href="{{ route('invoices.download', $sale->invoice) }}" class="app-btn-ghost text-slate-600 hover:text-slate-800">
                                            Facture
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500">Aucune vente enregistree.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>
