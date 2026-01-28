<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Mouvements de stock</h2>
            <p class="text-sm text-gray-500">Entrees, sorties et ajustements en un coup d'oeil.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Nouveau mouvement</h3>
            </div>

            <div class="px-6 py-4">
                <form wire:submit.prevent="saveMovement" class="grid gap-4 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Produit</label>
                    <select wire:model.live="productId" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selectionner</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('productId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Type</label>
                    <select wire:model.live="type" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                        <option value="in">Entree</option>
                        <option value="out">Sortie</option>
                        <option value="adjustment">Ajustement</option>
                    </select>
                    @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Quantite</label>
                    <input type="number" wire:model.live="quantity" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Raison</label>
                    <input type="text" wire:model.live="reason" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Date</label>
                    <input type="date" wire:model.live="occurred_at" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('occurred_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 lg:col-span-4">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                        Enregistrer
                    </button>
                    <button type="button" wire:click="resetForm" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200">
                        Reinitialiser
                    </button>
                </div>
            </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Historique</h3>
                    <p class="text-sm text-gray-500">Filtrez par produit.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un produit..." class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Produit</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Quantite</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Raison</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $movement->product?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    @if ($movement->type === 'in')
                                        Entree
                                    @elseif ($movement->type === 'out')
                                        Sortie
                                    @else
                                        Ajustement
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $movement->quantity }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $movement->reason ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $movement->occurred_at?->format('Y-m-d') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Aucun mouvement enregistre.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</div>
