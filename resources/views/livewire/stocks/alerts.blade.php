<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Alertes de stock</h2>
            <p class="app-subtitle">Produits sous le seuil et suggestions de reapprovisionnement.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Filtres</h3>
                    <p class="app-card-subtitle">Affinez la liste des produits a faible stock.</p>
                </div>
                <div class="text-sm text-slate-500">
                    Fenetre: {{ $windowDays }} jours · Couverture: {{ $coverageDays }} jours
                </div>
            </div>

            <div class="app-card-body">
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="app-label">Recherche</label>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nom, SKU, code-barres" class="app-input" />
                    </div>

                    <div>
                        <label class="app-label">Categorie</label>
                        <select wire:model.live="categoryId" class="app-select">
                            <option value="">Toutes</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="app-label">Fournisseur</label>
                        <select wire:model.live="supplierId" class="app-select">
                            <option value="">Tous</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
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
                    <h3 class="app-card-title">Produits a reapprovisionner</h3>
                    <p class="app-card-subtitle">{{ $products->total() }} produits sous le seuil.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @error('selected')
                        <span class="text-sm text-rose-600">{{ $message }}</span>
                    @enderror
                    <button type="button" wire:click="createPurchase" class="app-btn-primary">
                        Creer un achat
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Produit</th>
                            <th>Categorie</th>
                            <th>Stock</th>
                            <th>Seuil</th>
                            <th>Reappro</th>
                            <th>Suggere</th>
                            <th>Devise</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($products as $product)
                            @php
                                $suggestedQty = $suggested[$product->id] ?? 0;
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" value="{{ $product->id }}" wire:model.live="selected" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                                </td>
                                <td class="font-semibold text-slate-900">{{ $product->name }}</td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>{{ $product->stock?->quantity ?? 0 }}</td>
                                <td>{{ $product->min_stock ?? 0 }}</td>
                                <td>{{ $product->reorder_qty ?? 0 }}</td>
                                <td>{{ $suggestedQty > 0 ? $suggestedQty : '—' }}</td>
                                <td>{{ $product->currency ?? 'CDF' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500">Aucune alerte stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
