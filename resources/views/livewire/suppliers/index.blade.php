<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Fournisseurs</h2>
            <p class="app-subtitle">Centralisez vos partenaires d'achat.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">
                    {{ $supplierId ? 'Modifier un fournisseur' : 'Nouveau fournisseur' }}
                </h3>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="saveSupplier" class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <label class="app-label">Nom</label>
                    <input type="text" wire:model.live="name" class="app-input" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Contact</label>
                    <input type="text" wire:model.live="contact_name" class="app-input" />
                    @error('contact_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Telephone</label>
                    <input type="text" wire:model.live="phone" class="app-input" />
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="app-label">Email</label>
                    <input type="email" wire:model.live="email" class="app-input" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="lg:col-span-3">
                    <label class="app-label">Adresse</label>
                    <input type="text" wire:model.live="address" class="app-input" />
                    @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 lg:col-span-3">
                    <button type="submit" class="app-btn-primary">
                        {{ $supplierId ? 'Mettre a jour' : 'Ajouter' }}
                    </button>
                    @if ($supplierId)
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
                    <h3 class="app-card-title">Liste des fournisseurs</h3>
                    <p class="app-card-subtitle">Contact et coordination rapide.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Contact</th>
                            <th>Telephone</th>
                            <th>Email</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $supplier->name }}</td>
                                <td>{{ $supplier->contact_name ?? '—' }}</td>
                                <td>{{ $supplier->phone ?? '—' }}</td>
                                <td>{{ $supplier->email ?? '—' }}</td>
                                <td class="text-right">
                                    <button type="button" wire:click="editSupplier({{ $supplier->id }})" class="app-btn-ghost text-teal-600 hover:text-teal-700">Modifier</button>
                                    <button type="button" onclick="return confirm('Supprimer ce fournisseur ?') || event.stopImmediatePropagation()" wire:click="deleteSupplier({{ $supplier->id }})" class="app-btn-ghost text-rose-600 hover:text-rose-700">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">Aucun fournisseur trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
</div>
