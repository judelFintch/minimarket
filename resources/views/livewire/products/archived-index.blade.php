<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Produits archives</h2>
            <p class="app-subtitle">Les articles archives sont retires de la vente et de la liste standard.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Archive produits</h3>
                    <p class="app-card-subtitle">Consultez et restaurez uniquement les produits archives.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('products.index') }}" wire:navigate class="app-btn-secondary">Produits actifs</a>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Categorie</th>
                            <th>Code-barres</th>
                            <th>SKU</th>
                            <th>Unite</th>
                            <th>Stock</th>
                            <th>Seuil</th>
                            <th>Reappro</th>
                            <th>Prix vente</th>
                            <th>Archive le</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($products as $product)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $product->name }}</td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>{{ $product->barcode ?? '—' }}</td>
                                <td>{{ $product->sku ?? '—' }}</td>
                                <td>{{ $product->unitLabel() }}</td>
                                <td>{{ number_format((float) ($product->stock?->quantity ?? 0), 2) }}</td>
                                <td>{{ $product->min_stock ?? 0 }}</td>
                                <td>{{ $product->reorder_qty ?? 0 }}</td>
                                <td>{{ $product->sale_price !== null ? number_format($product->sale_price, 2) : '—' }}</td>
                                <td>{{ $product->archived_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-right">
                                    <button type="button" wire:click="restoreProduct({{ $product->id }})" class="app-btn-ghost text-amber-600 hover:text-amber-700">Restaurer</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-6 text-center text-sm text-slate-500">Aucun produit archive.</td>
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
