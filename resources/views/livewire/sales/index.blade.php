<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Ventes</h2>
            <p class="text-sm text-gray-500">Gestion des ventes et emission de factures.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <form wire:submit.prevent="saveSale" class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Panier</h3>
                            <p class="text-sm text-gray-500">Ajoutez des articles a la vente.</p>
                        </div>
                        <button type="button" wire:click="addItem" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                            Ajouter une ligne
                        </button>
                    </div>

                    <div class="px-6 py-4">
                        @error('items') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="space-y-3">
                            @foreach ($items as $index => $item)
                                <div class="grid items-end gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4 lg:grid-cols-12">
                                    <div class="lg:col-span-5">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Produit</label>
                                        <select wire:model.live="items.{{ $index }}.product_id" class="mt-2 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Selectionner</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("items.$index.product_id") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-2">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Quantite</label>
                                        <input type="number" min="1" wire:model.live="items.{{ $index }}.quantity" class="mt-2 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                                        @error("items.$index.quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-3">
                                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Prix unitaire</label>
                                        <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.unit_price" class="mt-2 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                                        @error("items.$index.unit_price") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="lg:col-span-2 flex items-center justify-between gap-2">
                                        <div class="text-right">
                                            <div class="text-xs uppercase tracking-wide text-gray-400">Total</div>
                                            <div class="text-sm font-semibold text-gray-900">
                                                {{ number_format(((int) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0)), 2) }}
                                            </div>
                                        </div>
                                        <button type="button" wire:click="removeItem({{ $index }})" class="text-xs font-semibold text-red-600 hover:text-red-700">
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
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm lg:sticky lg:top-24">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Resume</h3>
                        <p class="text-sm text-gray-500">Infos client et total.</p>
                    </div>

                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Client</label>
                                <input type="text" wire:model.live="customer_name" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                                @error('customer_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Date</label>
                                <input type="date" wire:model.live="sold_at" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                                @error('sold_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                                <div class="text-xs uppercase tracking-wide text-gray-400">Total</div>
                                <div class="text-2xl font-semibold text-gray-900">{{ number_format($total, 2) }}</div>
                            </div>

                            <div class="flex flex-col gap-3">
                                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                                    Enregistrer la vente
                                </button>
                                <button type="button" wire:click="resetForm" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200">
                                    Reinitialiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Historique des ventes</h3>
                    <p class="text-sm text-gray-500">Suivi rapide des ventes et factures PDF.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Articles</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Facture</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $sale->reference }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $sale->customer_name ?? 'Comptoir' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $sale->items_count }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($sale->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    @if ($sale->invoice)
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                                {{ ucfirst($sale->invoice->status) }}
                                            </span>
                                            <a href="{{ route('invoices.download', $sale->invoice) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                                                {{ $sale->invoice->invoice_number }}
                                            </a>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $sale->sold_at?->format('Y-m-d') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Aucune vente enregistree.</td>
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
