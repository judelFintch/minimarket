<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Produits</h2>
            <p class="app-subtitle">Catalogue complet avec prix et stock initial.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">
                    {{ $productId ? 'Modifier un produit' : 'Nouveau produit' }}
                </h3>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="saveProduct" class="grid gap-4 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label class="app-label">Nom</label>
                    <input type="text" wire:model.live="name" class="app-input" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Categorie</label>
                    <select wire:model.live="categoryId" class="app-select">
                        <option value="">Sans categorie</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Unite</label>
                    <input type="text" wire:model.live="unit" placeholder="piece, kg, litre" class="app-input" />
                    @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">SKU</label>
                    <input type="text" wire:model.live="sku" class="app-input" />
                    @error('sku') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Code-barres</label>
                    <input type="text" wire:model.live="barcode" class="app-input" />
                    @error('barcode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Prix d'achat</label>
                    <input type="number" step="0.01" wire:model.live="cost_price" class="app-input" />
                    @error('cost_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Prix de vente</label>
                    <input type="number" step="0.01" wire:model.live="sale_price" class="app-input" />
                    @error('sale_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Stock initial</label>
                    <input type="number" min="0" wire:model.live="stock_quantity" class="app-input" />
                    @error('stock_quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 lg:col-span-4">
                    <button type="submit" class="app-btn-primary">
                        {{ $productId ? 'Mettre a jour' : 'Ajouter' }}
                    </button>
                    @if ($productId)
                        <button type="button" wire:click="resetForm" class="app-btn-secondary">
                            Annuler
                        </button>
                    @endif
                </div>
            </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Liste des produits</h3>
                    <p class="app-card-subtitle">Recherchez rapidement un produit.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" wire:model.live="showArchived" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                        Uniquement archives
                    </label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
                </div>
            </div>

            @if ($deleteError !== '')
                <div class="mx-6 mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $deleteError }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Categorie</th>
                            <th>SKU</th>
                            <th>Stock</th>
                            <th>Prix vente</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($products as $product)
                            <tr>
                                <td class="font-semibold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $product->name }}</span>
                                        @if ($product->archived_at)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Archive</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>{{ $product->sku ?? '—' }}</td>
                                <td>{{ $product->stock?->quantity ?? 0 }}</td>
                                <td>
                                    {{ $product->sale_price !== null ? number_format($product->sale_price, 2) : '—' }}
                                </td>
                                <td class="text-right">
                                    <button type="button" wire:click="editProduct({{ $product->id }})" class="app-btn-ghost text-teal-600 hover:text-teal-700">Modifier</button>
                                    @if ($product->archived_at)
                                        <button type="button" wire:click="restoreProduct({{ $product->id }})" class="app-btn-ghost text-amber-600 hover:text-amber-700">Restaurer</button>
                                    @else
                                        <button type="button" onclick="return confirm('Archiver ce produit ?') || event.stopImmediatePropagation()" wire:click="deleteProduct({{ $product->id }})" class="app-btn-ghost text-rose-600 hover:text-rose-700">Archiver</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">Aucun produit trouve.</td>
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
