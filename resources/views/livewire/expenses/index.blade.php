<div class="space-y-8">
    <x-slot name="header">
        <div>
            <h2 class="app-title">Depenses</h2>
            <p class="app-subtitle">Gestion des depenses et sorties d'argent.</p>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-8">
        <div class="app-card">
            <div class="app-card-header">
                <h3 class="app-card-title">{{ $expenseId ? 'Modifier une depense' : 'Nouvelle depense' }}</h3>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="saveExpense" class="grid gap-4 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label class="app-label">Titre</label>
                        <input type="text" wire:model.live="title" class="app-input" />
                        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Categorie</label>
                        <select wire:model.live="categoryId" class="app-select">
                            <option value="">Sans categorie</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Date depense</label>
                        <input type="date" wire:model.live="incurred_at" class="app-input" />
                        @error('incurred_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Montant</label>
                        <input type="number" step="0.01" wire:model.live="amount" class="app-input" />
                        @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Devise</label>
                        <select wire:model.live="currency" class="app-select">
                            <option value="CDF">CDF</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                        @error('currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="app-label">Description</label>
                        <input type="text" wire:model.live="description" class="app-input" />
                        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-2">
                        <label class="app-label">Justificatif (PDF/JPG/PNG)</label>
                        <input type="file" wire:model="receipt" class="app-input" />
                        @error('receipt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-4">
                        <label class="app-label">Notes</label>
                        <textarea wire:model.live="notes" rows="2" class="app-input"></textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="lg:col-span-4 rounded-xl border border-slate-200/70 bg-slate-50 px-4 py-3">
                        <div class="text-sm font-semibold text-slate-700">Paiement initial (optionnel)</div>
                        <div class="mt-3 grid gap-3 md:grid-cols-4">
                            <div>
                                <label class="app-label">Montant</label>
                                <input type="number" step="0.01" wire:model.live="initial_payment_amount" class="app-input" />
                                @error('initial_payment_amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="app-label">Methode</label>
                                <select wire:model.live="initial_payment_method" class="app-select">
                                    <option value="">--</option>
                                    <option value="cash">Cash</option>
                                    <option value="mobile">Mobile</option>
                                    <option value="card">Carte</option>
                                    <option value="bank">Banque</option>
                                </select>
                            </div>
                            <div>
                                <label class="app-label">Date paiement</label>
                                <input type="date" wire:model.live="initial_paid_at" class="app-input" />
                            </div>
                            <div>
                                <label class="app-label">Notes paiement</label>
                                <input type="text" wire:model.live="initial_payment_notes" class="app-input" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 lg:col-span-4">
                        <button type="submit" class="app-btn-primary">
                            {{ $expenseId ? 'Mettre a jour' : 'Ajouter' }}
                        </button>
                        @if ($expenseId)
                            <button type="button" wire:click="resetForm" class="app-btn-secondary">Annuler</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Enregistrer une sortie d'argent</h3>
                    <p class="app-card-subtitle">Ajoutez un paiement sur une depense existante.</p>
                </div>
            </div>

            <div class="app-card-body">
                <form wire:submit.prevent="savePayment" class="grid gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="app-label">Depense</label>
                        <select wire:model.live="paymentExpenseId" class="app-select">
                            <option value="">Selectionner une depense</option>
                            @foreach ($expenseOptions as $expense)
                                <option value="{{ $expense->id }}">{{ $expense->title }} ({{ number_format($expense->amount, 2) }} {{ $expense->currency }})</option>
                            @endforeach
                        </select>
                        @error('paymentExpenseId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Montant</label>
                        <input type="number" step="0.01" wire:model.live="payment_amount" class="app-input" />
                        @error('payment_amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Date paiement</label>
                        <input type="date" wire:model.live="paid_at" class="app-input" />
                        @error('paid_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="app-label">Methode</label>
                        <select wire:model.live="payment_method" class="app-select">
                            <option value="">--</option>
                            <option value="cash">Cash</option>
                            <option value="mobile">Mobile</option>
                            <option value="card">Carte</option>
                            <option value="bank">Banque</option>
                        </select>
                        @error('payment_method') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-4">
                        <label class="app-label">Notes</label>
                        <input type="text" wire:model.live="payment_notes" class="app-input" />
                        @error('payment_notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-4">
                        <button type="submit" class="app-btn-primary">Ajouter le paiement</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="app-card">
            <div class="app-card-header">
                <div>
                    <h3 class="app-card-title">Liste des depenses</h3>
                    <p class="app-card-subtitle">Suivi des montants payes et restants.</p>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Rechercher..." class="app-input sm:max-w-xs" />
            </div>

            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Depense</th>
                            <th>Categorie</th>
                            <th>Montant</th>
                            <th>Paye</th>
                            <th>Solde</th>
                            <th>Date</th>
                            <th>Justificatif</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @forelse ($expenses as $expense)
                            @php
                                $paid = (float) ($expense->payments_sum_amount ?? 0);
                                $balance = (float) $expense->amount - $paid;
                            @endphp
                            <tr>
                                <td class="font-semibold text-slate-900">{{ $expense->title }}</td>
                                <td>{{ $expense->category?->name ?? '—' }}</td>
                                <td>{{ number_format($expense->amount, 2) }} {{ $expense->currency }}</td>
                                <td>{{ number_format($paid, 2) }} {{ $expense->currency }}</td>
                                <td>{{ number_format(max(0, $balance), 2) }} {{ $expense->currency }}</td>
                                <td>{{ $expense->incurred_at?->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    @php $receiptUrl = $this->getReceiptUrl($expense->receipt_path); @endphp
                                    @if ($receiptUrl)
                                        <a href="{{ $receiptUrl }}" target="_blank" class="text-sm text-teal-600 hover:text-teal-700">Voir</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-right">
                                    <button type="button" wire:click="editExpense({{ $expense->id }})" class="app-btn-ghost text-teal-600 hover:text-teal-700">Modifier</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500">Aucune depense.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
</div>
