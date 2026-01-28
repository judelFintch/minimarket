<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Achats</h2>
            <p class="text-sm text-gray-500">Receptions fournisseurs et mise a jour du stock.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Nouvel achat</h3>
            </div>

            <div class="px-6 py-4">
                <form wire:submit.prevent="savePurchase" class="space-y-4">
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Fournisseur</label>
                        <select wire:model.live="supplier_id" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Selectionner</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Date</label>
                        <input type="date" wire:model.live="purchased_at" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                        @error('purchased_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-gray-700">Articles</h4>
                        <button type="button" wire:click="addItem" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900">
                            Ajouter une ligne
                        </button>
                    </div>

                    @error('items') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                    @foreach ($items as $index => $item)
                        <div class="grid items-end gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4 lg:grid-cols-12">
                            <div class="lg:col-span-5">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Produit</label>
                                <select wire:model.live="items.{{ $index }}.product_id" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selectionner</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error("items.$index.product_id") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Quantite</label>
                                <input type="number" min="1" wire:model.live="items.{{ $index }}.quantity" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                                @error("items.$index.quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-3">
                                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Cout unitaire</label>
                                <input type="number" step="0.01" min="0" wire:model.live="items.{{ $index }}.unit_cost" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                                @error("items.$index.unit_cost") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="lg:col-span-2 flex items-center justify-between gap-2">
                                <div class="text-right">
                                    <div class="text-xs uppercase tracking-wide text-gray-400">Total</div>
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ number_format(((int) ($item['quantity'] ?? 0)) * ((float) ($item['unit_cost'] ?? 0)), 2) }}
                                    </div>
                                </div>
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-xs font-semibold text-red-600 hover:text-red-700">
                                    Retirer
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="text-xs uppercase tracking-wide text-gray-400">Total achat</div>
                        <div class="text-2xl font-semibold text-gray-900">{{ number_format($total, 2) }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                            Enregistrer l'achat
                        </button>
                        <button type="button" wire:click="resetForm" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200">
                            Reinitialiser
                        </button>
                    </div>
                </div>
            </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Historique des achats</h3>
                    <p class="text-sm text-gray-500">Suivi des receptions.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Fournisseur</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($purchases as $purchase)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $purchase->reference }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $purchase->supplier?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ number_format($purchase->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $purchase->purchased_at?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Aucun achat enregistre.</td>
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
