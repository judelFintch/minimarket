<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Utilisateurs</h2>
            <p class="app-subtitle">Gestion des comptes et roles.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">
                    {{ $userId ? 'Modifier un utilisateur' : 'Nouvel utilisateur' }}
                </h3>
            </div>
            <div class="app-card-body">
                <form wire:submit.prevent="saveUser" class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="app-label">Nom</label>
                        <input type="text" wire:model.live="name" class="app-input" />
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="app-label">Email</label>
                        <input type="email" wire:model.live="email" class="app-input" />
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="app-label">Role</label>
                        <select wire:model.live="role" class="app-select">
                            <option value="admin">Admin</option>
                            <option value="vendeur">Vendeur</option>
                            <option value="vendeur_simple">Vendeur simple</option>
                        </select>
                        @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="app-label">Mot de passe {{ $userId ? '(laisser vide pour garder)' : '' }}</label>
                        <input type="password" wire:model.live="password" class="app-input" />
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-3 sm:col-span-2">
                        <button type="submit" class="app-btn-primary">
                            {{ $userId ? 'Mettre a jour' : 'Ajouter' }}
                        </button>
                        @if ($userId)
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
                    <h3 class="app-card-title">Liste des utilisateurs</h3>
                    <p class="app-card-subtitle">Rechercher rapidement un compte.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($users as $user)
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role ?? 'vendeur' }}</td>
                                <td class="text-right">
                                    <button type="button" wire:click="editUser({{ $user->id }})" class="app-btn-ghost text-teal-600 hover:text-teal-700">Modifier</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Aucun utilisateur trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
