<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Fournisseurs</h2>
            <p class="text-sm text-gray-500">Centralisez vos partenaires d'achat.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $supplierId ? 'Modifier un fournisseur' : 'Nouveau fournisseur' }}
                </h3>
            </div>

            <div class="px-6 py-4">
                <form wire:submit.prevent="saveSupplier" class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Nom</label>
                    <input type="text" wire:model.live="name" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Contact</label>
                    <input type="text" wire:model.live="contact_name" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('contact_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Telephone</label>
                    <input type="text" wire:model.live="phone" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Email</label>
                    <input type="email" wire:model.live="email" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Adresse</label>
                    <input type="text" wire:model.live="address" class="mt-2 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500" />
                    @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 lg:col-span-3">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300">
                        {{ $supplierId ? 'Mettre a jour' : 'Ajouter' }}
                    </button>
                    @if ($supplierId)
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
                    <h3 class="text-lg font-semibold text-gray-900">Liste des fournisseurs</h3>
                    <p class="text-sm text-gray-500">Contact et coordination rapide.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Nom</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Contact</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Telephone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-700">Email</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $supplier->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $supplier->contact_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $supplier->phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $supplier->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" wire:click="editSupplier({{ $supplier->id }})" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Modifier</button>
                                    <button type="button" onclick="return confirm('Supprimer ce fournisseur ?') || event.stopImmediatePropagation()" wire:click="deleteSupplier({{ $supplier->id }})" class="ml-3 text-sm font-semibold text-red-600 hover:text-red-700">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Aucun fournisseur trouve.</td>
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
