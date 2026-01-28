<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Categories</h2>
            <p class="app-subtitle">Organisez vos produits pour un suivi clair.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">
                    {{ $categoryId ? 'Modifier une categorie' : 'Nouvelle categorie' }}
                </h3>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="saveCategory" class="grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <label class="app-label">Nom</label>
                        <input type="text" wire:model.live="name" class="app-input" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-3">
                        <label class="app-label">Description</label>
                        <textarea wire:model.live="description" rows="3" class="app-textarea"></textarea>
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 lg:col-span-3">
                        <button type="submit" class="app-btn-primary">
                            {{ $categoryId ? 'Mettre a jour' : 'Ajouter' }}
                        </button>
                        @if ($categoryId)
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
                    <h3 class="app-card-title">Liste des categories</h3>
                    <p class="app-card-subtitle">Recherche rapide et actions directes.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Description</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $category->name }}</td>
                                <td>{{ $category->description ?? '—' }}</td>
                                <td class="text-right">
                                    <button type="button" wire:click="editCategory({{ $category->id }})" class="app-btn-ghost text-teal-600 hover:text-teal-700">Modifier</button>
                                    <button type="button" onclick="return confirm('Supprimer cette categorie ?') || event.stopImmediatePropagation()" wire:click="deleteCategory({{ $category->id }})" class="app-btn-ghost text-rose-600 hover:text-rose-700">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">Aucune categorie trouvee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</div>
