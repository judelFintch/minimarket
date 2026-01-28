<div
    class="space-y-8"
    x-data
    x-on:keydown.window="
        if (($event.ctrlKey || $event.metaKey) && $event.key === 'Enter' && ! $event.shiftKey) { $event.preventDefault(); $wire.saveSale(); }
        if (($event.ctrlKey || $event.metaKey) && $event.key === 'Enter' && $event.shiftKey) { $event.preventDefault(); $wire.savePending(); }
        if (($event.ctrlKey || $event.metaKey) && ($event.key === 'i' || $event.key === 'I')) { $event.preventDefault(); $wire.addItem(); }
    "
>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
            <h2 class="app-title">Ventes</h2>
            <p class="app-subtitle">Gestion des ventes et emission de factures.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('sales.history') }}" wire:navigate class="app-btn-secondary">Historique</a>
                <a href="{{ route('dashboard') }}" wire:navigate class="app-btn-ghost">Dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Catalogue rapide</h3>
                    <p class="app-card-subtitle">Cliquez sur un produit pour l'ajouter au panier.</p>
                </div>
                <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
                    <input type="text" wire:model.live.debounce.300ms="catalogSearch" placeholder="Rechercher..." class="app-input sm:w-56" />
                    <select wire:model.live="catalogCategory" class="app-select sm:w-48">
                        <option value="0">Toutes categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="catalogStock" class="app-select sm:w-40">
                        <option value="">Stock</option>
                        <option value="low">Faible</option>
                        <option value="out">Rupture</option>
                    </select>
                </div>
            </div>
            <div class="app-card-body">
                @if ($favoriteProducts->isNotEmpty())
                    <div class="mb-6">
                        <div class="mb-3 text-sm font-semibold text-slate-700">Favoris</div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($favoriteProducts as $product)
                                @php
                                    $stockQty = $product->stock?->quantity ?? 0;
                                    $price = $product->promo_price !== null ? $product->promo_price : $product->sale_price;
                                @endphp
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $product->name }}</div>
                                        <div class="text-xs text-slate-500">{{ number_format($price ?? 0, 2) }} · Stock {{ $stockQty }}</div>
                                    </div>
                                    <button type="button" wire:click="addProduct({{ $product->id }})" class="app-btn-primary">
                                        Ajouter
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($frequentProducts->isNotEmpty())
                    <div class="mb-6">
                        <div class="mb-3 text-sm font-semibold text-slate-700">Produits frequents</div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @foreach ($frequentProducts as $product)
                                @php
                                    $stockQty = $product->stock?->quantity ?? 0;
                                    $price = $product->promo_price !== null ? $product->promo_price : $product->sale_price;
                                @endphp
                                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $product->name }}</div>
                                        <div class="text-xs text-slate-500">{{ number_format($price ?? 0, 2) }} · Stock {{ $stockQty }}</div>
                                    </div>
                                    <button type="button" wire:click="addProduct({{ $product->id }})" class="app-btn-secondary">
                                        Ajouter
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse ($catalogProducts as $product)
                        @php
                            $stockQty = $product->stock?->quantity ?? 0;
                            $price = $product->promo_price !== null ? $product->promo_price : $product->sale_price;
                        @endphp
                        <div
                            role="button"
                            tabindex="0"
                            wire:click="addProduct({{ $product->id }})"
                            class="group flex h-full cursor-pointer flex-col justify-between rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-md">
                            <div class="space-y-3">
                                <div class="relative overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-28 w-full object-cover" />
                                    @else
                                        <div class="flex h-28 w-full items-center justify-center bg-gradient-to-br from-teal-50 via-white to-amber-50 text-xl font-semibold text-slate-400">
                                            {{ strtoupper(substr($product->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-slate-900">{{ $product->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $product->category?->name ?? 'Sans categorie' }}</div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        @if ($product->promo_label || $product->promo_price !== null)
                                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-rose-700">
                                                {{ $product->promo_label ?? 'Promo' }}
                                            </span>
                                        @endif
                                        <button type="button"
                                            wire:click.stop="toggleFavorite({{ $product->id }})"
                                            class="rounded-full border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-500 hover:text-amber-600">
                                            {{ in_array($product->id, $favoriteIds, true) ? '★' : '☆' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-lg font-semibold text-teal-700">{{ number_format($price ?? 0, 2) }}</div>
                                    @if ($product->promo_price !== null)
                                        <div class="text-xs text-slate-400 line-through">{{ number_format($product->sale_price ?? 0, 2) }}</div>
                                    @endif
                                </div>
                                <div class="text-xs font-semibold {{ $stockQty <= 0 ? 'text-rose-600' : ($stockQty <= 5 ? 'text-amber-600' : 'text-slate-500') }}">
                                    Stock {{ $stockQty }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">Aucun produit trouve.</div>
                    @endforelse
                </div>

                @if ($catalogProducts->count() < $catalogTotal)
                    <div class="mt-6 flex justify-center">
                        <button type="button" wire:click="loadMoreCatalog" class="app-btn-secondary">
                            Charger plus
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <form wire:submit.prevent="saveSale" class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="app-card">
                    <div class="app-card-header">
                        <div>
                            <h3 class="app-card-title">Panier</h3>
                            <p class="app-card-subtitle">Ajoutez des articles a la vente.</p>
                            <p class="mt-1 text-xs text-slate-400">
                                Raccourcis: Ctrl/Cmd + Entrer (encaisser), Ctrl/Cmd + Maj + Entrer (attente), Ctrl/Cmd + I (nouvelle ligne)
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="addItem" class="app-btn-primary">
                                Ajouter une ligne
                            </button>
                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500">
                                <span class="uppercase tracking-wider">Scan</span>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="barcodeInput"
                                    placeholder="Code-barres"
                                    class="w-28 border-0 bg-transparent p-0 text-xs text-slate-700 focus:ring-0"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="app-card-body">
                        @error('items') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="mb-4 grid gap-3 rounded-2xl border border-slate-200/70 bg-slate-50/80 p-4">
                            <div>
                                <label class="app-label">Recherche rapide</label>
                                <input type="text" wire:model.live.debounce.300ms="productSearch" placeholder="Nom du produit" class="app-input" />
                            </div>
                            @if ($productSearch !== '')
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @forelse ($filteredProducts as $product)
                                        <button type="button" wire:click="addProduct({{ $product->id }})" class="app-btn-secondary justify-between">
                                            <span>{{ $product->name }}</span>
                                            <span class="text-xs text-slate-500">Stock {{ $product->stock?->quantity ?? 0 }}</span>
                                        </button>
                                    @empty
                                        <div class="text-sm text-slate-500">Aucun produit trouve.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>

                        <div class="space-y-3">
                            @foreach ($items as $index => $item)
                                <div class="grid items-end gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm lg:grid-cols-12">
                                    <div class="lg:col-span-4">
                                        <label class="app-label">Produit</label>
                                        <select wire:model.live="items.{{ $index }}.product_id" class="app-select">
                                            <option value="">Selectionner</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">
                                                    {{ $product->name }} · Stock {{ $product->stock?->quantity ?? 0 }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.product_id") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-2">
                                        <label class="app-label">Quantite</label>
                                        <input type="number" min="1" wire:model.live="items.{{ $index }}.quantity" class="app-input" />
                                        @error("items.$index.quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-2">
                                        <div class="flex items-center justify-between">
                                            <label class="app-label">Prix unitaire</label>
                                            <span class="text-xs font-semibold text-emerald-600">Auto</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.unit_price" class="app-input bg-slate-100" readonly />
                                        @error("items.$index.unit_price") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-2">
                                        <label class="app-label">Remise %</label>
                                        <input type="number" min="0" max="100" step="0.01" wire:model.live="items.{{ $index }}.discount_rate" class="app-input" />
                                        @error("items.$index.discount_rate") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-2 flex items-center justify-between gap-2">
                                        <div class="text-right">
                                            <div class="text-xs uppercase tracking-wide text-slate-400">Total ligne</div>
                                            <div class="text-base font-semibold text-slate-900">
                                                @php
                                                    $lineBase = ((int) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0));
                                                    $lineDiscount = $lineBase * (((float) ($item['discount_rate'] ?? 0)) / 100);
                                                    $lineTotal = $lineBase - $lineDiscount;
                                                @endphp
                                                {{ number_format($lineTotal, 2) }}
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
                                <div class="text-xs uppercase tracking-wide text-slate-400">Sous-total</div>
                                <div class="text-2xl font-semibold text-slate-900">{{ number_format($totals['subtotal'], 2) }}</div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="app-label">Remise globale (%)</label>
                                    <input type="number" min="0" max="100" step="0.01" wire:model.live="discountRate" class="app-input" />
                                </div>
                                <div>
                                    <label class="app-label">TVA (%)</label>
                                    <input type="number" min="0" max="100" step="0.01" wire:model.live="taxRate" class="app-input" />
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-xs uppercase tracking-wide text-slate-400">Remise</div>
                                    <div class="text-lg font-semibold text-slate-900">- {{ number_format($totals['discountAmount'], 2) }}</div>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-xs uppercase tracking-wide text-slate-400">TVA</div>
                                    <div class="text-lg font-semibold text-slate-900">+ {{ number_format($totals['taxAmount'], 2) }}</div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-900 px-4 py-3 text-white">
                                <div class="text-xs uppercase tracking-wide text-white/70">Total a payer</div>
                                <div class="text-2xl font-semibold">{{ number_format($totals['total'], 2) }}</div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <button type="submit" class="app-btn-primary">
                                    Encaisser
                                </button>
                                <button type="button" wire:click="savePending" class="app-btn-secondary">
                                    Mettre en attente
                                </button>
                            </div>
                            <button type="button" wire:click="resetForm" class="app-btn-ghost">
                                Reinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
