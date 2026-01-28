<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Achats</h2>
            <p class="app-subtitle">Receptions fournisseurs et mise a jour du stock.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">Nouvel achat</h3>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="savePurchase" class="space-y-4">
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label class="app-label">Fournisseur</label>
                        <select wire:model.live="supplier_id" class="app-select">
                            <option value="">Selectionner</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Date</label>
                        <input type="date" wire:model.live="purchased_at" class="app-input" />
                        @error('purchased_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-slate-700">Articles</h4>
                        <button type="button" wire:click="addItem" class="app-btn-secondary">
                            Ajouter une ligne
                        </button>
                    </div>

                    @error('items') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

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
                                <label class="app-label">Cout unitaire</label>
                                <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.unit_cost" class="app-input" />
                                @error("items.$index.unit_cost") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2 flex items-center justify-between gap-2">
                                <div class="text-right">
                                    <div class="text-xs uppercase tracking-wide text-slate-400">Total</div>
                                    <div class="text-sm font-semibold text-slate-900">
                                        {{ number_format(((int) ($item['quantity'] ?? 0)) * ((float) ($item['unit_cost'] ?? 0)), 2) }}
                                    </div>
                                </div>
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                                    Retirer
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-400">Total achat</div>
                        <div class="text-2xl font-semibold text-slate-900">{{ number_format($total, 2) }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="app-btn-primary">
                            Enregistrer l'achat
                        </button>
                        <button type="button" wire:click="resetForm" class="app-btn-secondary">
                            Reinitialiser
                        </button>
                    </div>
                </div>
            </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Historique des achats</h3>
                    <p class="app-card-subtitle">Suivi des receptions.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Fournisseur</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($purchases as $purchase)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $purchase->reference }}</td>
                                <td>{{ $purchase->supplier?->name ?? '—' }}</td>
                                <td>{{ number_format($purchase->total_amount, 2) }}</td>
                                <td>{{ $purchase->purchased_at?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Aucun achat enregistre.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</div>
