<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Sorties de stock</h2>
            <p class="app-subtitle">Sorties d'articles hors vente (pertes, dons, usages internes).</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">Nouvelle sortie</h3>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="saveStockOut" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="app-label">Date</label>
                            <input type="date" wire:model.live="occurred_at" class="app-input" />
                            @error('occurred_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="app-label">Raison</label>
                            <input type="text" wire:model.live="reason" placeholder="casse, usage interne..." class="app-input" />
                            @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="app-label">Notes</label>
                            <input type="text" wire:model.live="notes" class="app-input" />
                            @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="app-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantite</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach ($items as $index => $item)
                                    <tr>
                                        <td>
                                            <select wire:model.live="items.{{ $index }}.product_id" class="app-select">
                                                <option value="">Selectionner un produit</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                            @error("items.$index.product_id") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.quantity" class="app-input" />
                                            @error("items.$index.quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="text-right">
                                            <button type="button" wire:click="removeItem({{ $index }})" class="app-btn-ghost text-rose-600 hover:text-rose-700">Supprimer</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" wire:click="addItem" class="app-btn-secondary">Ajouter un produit</button>
                        <button type="submit" class="app-btn-primary">Enregistrer la sortie</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Historique des sorties</h3>
                    <p class="app-card-subtitle">Toutes les sorties enregistrees.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Raison</th>
                            <th>Quantite totale</th>
                            <th>Articles</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($stockOuts as $stockOut)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $stockOut->reference }}</td>
                                <td>{{ $stockOut->occurred_at?->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $stockOut->reason ?? '—' }}</td>
                                <td>{{ number_format($stockOut->total_quantity, 2) }}</td>
                                <td class="text-sm text-slate-600">
                                    @foreach ($stockOut->items as $item)
                                        <div>{{ $item->product?->name }} ({{ number_format($item->quantity, 2) }})</div>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Aucune sortie.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $stockOuts->links() }}
            </div>
        </div>
    </div>
</div>
