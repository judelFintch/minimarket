<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Categories de depenses</h2>
            <p class="app-subtitle">Organisez vos depenses par categorie.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-4xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">{{ $categoryId ? 'Modifier une categorie' : 'Nouvelle categorie' }}</h3>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="saveCategory" class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="app-label">Nom</label>
                        <input type="text" wire:model.live="name" class="app-input" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-end gap-3">
                        <button type="submit" class="app-btn-primary">
                            {{ $categoryId ? 'Mettre a jour' : 'Ajouter' }}
                        </button>
                        @if ($categoryId)
                            <button type="button" wire:click="resetForm" class="app-btn-secondary">Annuler</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Liste des categories</h3>
                    <p class="app-card-subtitle">Recherchez rapidement une categorie.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $category->name }}</td>
                                <td class="text-right">
                                    <button type="button" wire:click="editCategory({{ $category->id }})" class="app-btn-ghost text-teal-600 hover:text-teal-700">Modifier</button>
                                    <button type="button" onclick="return confirm('Supprimer cette categorie ?') || event.stopImmediatePropagation()" wire:click="deleteCategory({{ $category->id }})" class="app-btn-ghost text-rose-600 hover:text-rose-700">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">Aucune categorie.</td>
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
