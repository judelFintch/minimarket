<div
    class="space-y-8 sales-screen"
    x-data="{ searchCount: {{ $filteredProducts->count() }}, activeIndex: 0, screenMode: @entangle('screenMode').live }"
    x-bind:class="`sales-screen-${screenMode}`"
    x-init="$nextTick(() => $refs.barcode?.focus())"
    x-on:keydown.window="
        if (($event.ctrlKey || $event.metaKey) && $event.key === 'Enter' && ! $event.shiftKey) { $event.preventDefault(); $wire.saveSale(); }
        if (($event.ctrlKey || $event.metaKey) && $event.key === 'Enter' && $event.shiftKey) { $event.preventDefault(); $wire.savePending(); }
        if (($event.ctrlKey || $event.metaKey) && ($event.key === 'i' || $event.key === 'I')) { $event.preventDefault(); $wire.addItem(); }
        if ($event.key === 'F2') { $event.preventDefault(); $refs.customer?.focus(); }
        if ($event.key === 'F4') { $event.preventDefault(); $refs.barcode?.focus(); }
    "
    x-on:notify.window="
        $dispatch('toast', { message: $event.detail.message, invoiceId: $event.detail.invoiceId });
        if ($event.detail.invoiceId) {
            if (window.__receiptWindow && !window.__receiptWindow.closed) {
                window.__receiptWindow.location = `/invoices/${$event.detail.invoiceId}/receipt`;
                window.__receiptWindow.focus();
                window.__receiptWindow = null;
            } else {
                window.open(`/invoices/${$event.detail.invoiceId}/receipt`, '_blank');
            }
        }
    "
    x-on:focus-barcode.window="$refs.barcode?.focus()"
>
    <div
        x-data="{ open: false, message: '', invoiceId: null }"
        x-on:toast.window="
            message = $event.detail.message || '';
            invoiceId = $event.detail.invoiceId || null;
            open = true;
            setTimeout(() => open = false, 3000);
        "
        x-show="open"
        class="fixed right-6 top-6 z-50 w-72 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 shadow-lg"
        style="display: none;"
    >
        <div class="flex items-center justify-between gap-3">
            <span x-text="message"></span>
            <template x-if="invoiceId">
                <a :href="`/invoices/${invoiceId}/receipt`" class="text-emerald-800 underline">Ticket</a>
            </template>
        </div>
    </div>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
            <h2 class="app-title">Ventes</h2>
            <p class="app-subtitle">Gestion des ventes et emission de factures.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
                    <span class="uppercase tracking-wider">Mode ecran</span>
                    <select wire:model.live="screenMode" wire:change="setScreenMode($event.target.value)" class="border-0 bg-transparent p-0 text-xs font-semibold text-slate-700 focus:ring-0">
                        <option value="pos">POS</option>
                        <option value="tablet">Tablette</option>
                        <option value="pc">PC</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>
                <a href="{{ route('sales.history') }}" wire:navigate class="app-btn-secondary">Historique</a>
                <a href="{{ route('dashboard') }}" wire:navigate class="app-btn-ghost">Dashboard</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8 sales-shell">
        <form wire:submit.prevent="saveSale" class="sales-grid grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-7 sales-cart">
                <div class="app-card sales-card">
                    <div class="app-card-header">
                        <div>
                            <h3 class="app-card-title">Panier</h3>
                            <p class="app-card-subtitle">Ajoutez des articles a la vente.</p>
                            <p class="mt-1 text-xs text-slate-400">
                                Raccourcis: Ctrl/Cmd + Entrer (encaisser), Ctrl/Cmd + Maj + Entrer (attente), Ctrl/Cmd + I (nouvelle ligne)
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 sales-cart-actions">
                            <button type="button" wire:click="addItem" class="app-btn-primary">
                                Ajouter une ligne
                            </button>
                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 sales-scan">
                                <span class="uppercase tracking-wider">Scan</span>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="barcodeInput"
                                    x-ref="barcode"
                                    placeholder="Code-barres"
                                    class="w-28 border-0 bg-transparent p-0 text-xs text-slate-700 focus:ring-0"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="app-card-body">
                        @error('items') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        @if ($favoriteProducts->isNotEmpty() || $frequentProducts->isNotEmpty())
                            <div class="mb-4">
                                <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Raccourcis</div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($favoriteProducts as $product)
                                        <button type="button" wire:click="addProduct({{ $product->id }})" class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                            ★ {{ $product->name }}
                                        </button>
                                    @endforeach
                                    @foreach ($frequentProducts as $product)
                                        <button type="button" wire:click="addProduct({{ $product->id }})" class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $product->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mb-4 grid gap-3 rounded-2xl border border-slate-200/70 bg-slate-50/80 p-4 sales-search">
                            <div>
                                <label class="app-label">Recherche rapide</label>
                                <input type="text"
                                    wire:model.live.debounce.300ms="productSearch"
                                    placeholder="Nom du produit"
                                    class="app-input"
                                    x-ref="productSearch"
                                    x-on:keydown.down.prevent="if (searchCount > 0) { activeIndex = Math.min(activeIndex + 1, searchCount - 1); }"
                                    x-on:keydown.up.prevent="if (searchCount > 0) { activeIndex = Math.max(activeIndex - 1, 0); }"
                                    x-on:keydown.enter.prevent="if (searchCount > 0) { document.getElementById('search-item-' + activeIndex)?.click(); }"
                                />
                            </div>
                            @if ($productSearch !== '')
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @forelse ($filteredProducts as $product)
                                        <button type="button"
                                            id="search-item-{{ $loop->index }}"
                                            wire:click="addProduct({{ $product->id }})"
                                            class="app-btn-secondary justify-between"
                                            :class="activeIndex === {{ $loop->index }} ? 'ring-2 ring-teal-300' : ''">
                                            <span>{{ $product->name }}</span>
                                            <span class="text-xs text-slate-500">Stock {{ $product->stock?->quantity ?? 0 }}</span>
                                        </button>
                                    @empty
                                        <div class="text-sm text-slate-500">Aucun produit trouve.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>

                        <div class="space-y-3 sales-lines">
                            @foreach ($items as $index => $item)
                                <div class="sales-line grid items-end gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm lg:grid-cols-12">
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
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="decrementQuantity({{ $index }})" class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-lg font-semibold text-slate-600 hover:bg-slate-50">
                                                -
                                            </button>
                                            <input type="number" min="1" wire:model.live="items.{{ $index }}.quantity" class="app-input" />
                                            <button type="button" wire:click="incrementQuantity({{ $index }})" class="h-9 w-9 rounded-xl border border-slate-200 bg-white text-lg font-semibold text-slate-600 hover:bg-slate-50">
                                                +
                                            </button>
                                        </div>
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

            <div class="lg:col-span-5 sales-summary">
                <div class="app-card sales-card lg:sticky lg:top-24">
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
                                <input type="text" wire:model.live="customer_name" class="app-input" x-ref="customer" />
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

                            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                                <div class="text-xs uppercase tracking-wide text-slate-400">Paiement rapide</div>
                                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                    <input type="number" min="0" step="0.01" wire:model.live="amountReceived" class="app-input" placeholder="Montant recu" />
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
                                        Rendu: {{ number_format($changeDue, 2) }}
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" wire:click="setAmountReceivedToTotal" class="app-btn-secondary">Montant exact</button>
                                    <button type="button" wire:click="setAmountReceived({{ $totals['total'] + 5 }})" class="app-btn-secondary">+5</button>
                                    <button type="button" wire:click="setAmountReceived({{ $totals['total'] + 10 }})" class="app-btn-secondary">+10</button>
                                    <button type="button" wire:click="setAmountReceived({{ $totals['total'] + 20 }})" class="app-btn-secondary">+20</button>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <button type="submit" class="app-btn-primary" x-on:click="window.__receiptWindow = window.open('about:blank', '_blank');">
                                    Encaisser
                                </button>
                                <button type="button" wire:click="savePending" class="app-btn-secondary">
                                    Mettre en attente
                                </button>
                            </div>
                            @if ($lastInvoiceId)
                                <a href="{{ route('invoices.receipt', $lastInvoiceId) }}" class="app-btn-ghost text-emerald-700 hover:text-emerald-800">
                                    Imprimer ticket
                                </a>
                            @endif
                            <button type="button" wire:click="resetForm" class="app-btn-ghost">
                                Reinitialiser
                            </button>

                            @if ($pendingSales->isNotEmpty())
                                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                                    <div class="text-xs uppercase tracking-wide text-amber-700">Ventes en attente</div>
                                    <div class="mt-2 space-y-2 text-sm">
                                        @foreach ($pendingSales as $sale)
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <div class="font-semibold text-slate-800">{{ $sale->reference }}</div>
                                                    <div class="text-xs text-slate-500">{{ $sale->items_count }} articles · {{ number_format($sale->total_amount, 2) }}</div>
                                                </div>
                                                <button type="button" wire:click="loadPendingSale({{ $sale->id }})" class="app-btn-secondary">
                                                    Reprendre
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
