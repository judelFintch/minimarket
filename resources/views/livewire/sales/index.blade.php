<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Ventes</h2>
            <p class="app-subtitle">Gestion des ventes et emission de factures.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <form wire:submit.prevent="saveSale" class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="app-card">
                    <div class="app-card-header">
                        <div>
                            <h3 class="app-card-title">Panier</h3>
                            <p class="app-card-subtitle">Ajoutez des articles a la vente.</p>
                        </div>
                        <button type="button" wire:click="addItem" class="app-btn-primary">
                            Ajouter une ligne
                        </button>
                    </div>

                    <div class="app-card-body">
                        @error('items') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="space-y-3">
                            @foreach ($items as $index => $item)
                                <div class="grid items-end gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-4 lg:grid-cols-12">
                                    <div class="lg:col-span-5">
                                        <label class="app-label">Produit</label>
                                        <select wire:model.live="items.{{ $index }}.product_id" class="app-select">
                                            <option value="">Selectionner</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.product_id") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-2">
                                        <label class="app-label">Quantite</label>
                                        <input type="number" min="1" wire:model.live="items.{{ $index }}.quantity" class="app-input" />
                                        @error("items.$index.quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-3">
                                        <label class="app-label">Prix unitaire</label>
                                        <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.unit_price" class="app-input" />
                                        @error("items.$index.unit_price") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-2 flex items-center justify-between gap-2">
                                        <div class="text-right">
                                            <div class="text-xs uppercase tracking-wide text-slate-400">Total</div>
                                            <div class="text-sm font-semibold text-slate-900">
                                                {{ number_format(((int) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0)), 2) }}
                                            </div>
                                        </div>
                                        <button type="button" wire:click="removeItem({{ $index }})" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                                            Retirer
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="app-card lg:sticky lg:top-24">
                    <div class="app-card-header">
                        <div>
                            <h3 class="app-card-title">Resume</h3>
                            <p class="app-card-subtitle">Infos client et total.</p>
                        </div>
                    </div>

                    <div class="app-card-body">
                        <div class="space-y-4">
                            <div>
                                <label class="app-label">Client</label>
                                <input type="text" wire:model.live="customer_name" class="app-input" />
                                @error('customer_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="app-label">Date</label>
                                <input type="date" wire:model.live="sold_at" class="app-input" />
                                @error('sold_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="text-xs uppercase tracking-wide text-slate-400">Total</div>
                                <div class="text-2xl font-semibold text-slate-900">{{ number_format($total, 2) }}</div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <button type="submit" class="app-btn-primary">
                                    Enregistrer la vente
                                </button>
                                <button type="button" wire:click="resetForm" class="app-btn-secondary">
                                    Reinitialiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Historique des ventes</h3>
                    <p class="app-card-subtitle">Suivi rapide des ventes et factures PDF.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Client</th>
                            <th>Articles</th>
                            <th>Total</th>
                            <th>Facture</th>
                            <th>Date</th>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">Aucune vente enregistree.</td>
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
