<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Produits</h2>
            <p class="text-sm text-gray-500">Catalogue complet avec prix et stock initial.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $productId ? 'Modifier un produit' : 'Nouveau produit' }}
                </h3>
            </div>

            <div class="px-6 py-4">
                <form wire:submit.prevent="saveProduct" class="grid gap-4 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Nom</label>
                    <input type="text" wire:model.live="name" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Categorie</label>
                    <select wire:model.live="categoryId" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sans categorie</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Unite</label>
                    <input type="text" wire:model.live="unit" placeholder="piece, kg, litre" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">SKU</label>
                    <input type="text" wire:model.live="sku" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('sku') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Code-barres</label>
                    <input type="text" wire:model.live="barcode" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('barcode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Prix d'achat</label>
                    <input type="number" step="0.01" wire:model.live="cost_price" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('cost_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Prix de vente</label>
                    <input type="number" step="0.01" wire:model.live="sale_price" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('sale_price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Stock initial</label>
                    <input type="number" min="0" wire:model.live="stock_quantity" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('stock_quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 lg:col-span-4">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                        {{ $productId ? 'Mettre a jour' : 'Ajouter' }}
                    </button>
                    @if ($productId)
                        <button type="button" wire:click="resetForm" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200">
                            Annuler
                        </button>
                    @endif
                </div>
            </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Liste des produits</h3>
                    <p class="text-sm text-gray-500">Recherchez rapidement un produit.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Produit</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Categorie</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Prix vente</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $product->sku ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $product->stock?->quantity ?? 0 }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $product->sale_price !== null ? number_format($product->sale_price, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" wire:click="editProduct({{ $product->id }})" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Modifier</button>
                                    <button type="button" onclick="return confirm('Supprimer ce produit ?') || event.stopImmediatePropagation()" wire:click="deleteProduct({{ $product->id }})" class="ml-3 text-sm font-semibold text-red-600 hover:text-red-700">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Aucun produit trouve.</td>
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
