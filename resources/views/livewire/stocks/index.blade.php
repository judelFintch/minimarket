<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Mouvements de stock</h2>
            <p class="app-subtitle">Entrees, sorties et ajustements en un coup d'oeil.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">Nouveau mouvement</h3>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="saveMovement" class="grid gap-4 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <label class="app-label">Produit</label>
                    <select wire:model.live="productId" class="app-select">
                        <option value="">Selectionner</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('productId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Type</label>
                    <select wire:model.live="type" class="app-select">
                        <option value="in">Entree</option>
                        <option value="out">Sortie</option>
                        <option value="adjustment">Ajustement</option>
                    </select>
                    @error('type') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Quantite</label>
                    <input type="number" wire:model.live="quantity" class="app-input" />
                    @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="lg:col-span-2">
                    <label class="app-label">Raison</label>
                    <input type="text" wire:model.live="reason" class="app-input" />
                    @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Date</label>
                    <input type="date" wire:model.live="occurred_at" class="app-input" />
                    @error('occurred_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 lg:col-span-4">
                    <button type="submit" class="app-btn-primary">
                        Enregistrer
                    </button>
                    <button type="button" wire:click="resetForm" class="app-btn-secondary">
                        Reinitialiser
                    </button>
                </div>
            </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Historique</h3>
                    <p class="app-card-subtitle">Filtrez par produit.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher un produit..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Type</th>
                            <th>Quantite</th>
                            <th>Raison</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $movement->product?->name ?? '—' }}</td>
                                <td>
                                    @if ($movement->type === 'in')
                                        Entree
                                    @elseif ($movement->type === 'out')
                                        Sortie
                                    @else
                                        Ajustement
                                    @endif
                                </td>
                                <td>{{ $movement->quantity }}</td>
                                <td>{{ $movement->reason ?? '—' }}</td>
                                <td>
                                    {{ $movement->occurred_at?->format('Y-m-d') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Aucun mouvement enregistre.</td>
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
