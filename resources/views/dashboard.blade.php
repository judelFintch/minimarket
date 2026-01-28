<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="app-title">Tableau de bord</h2>
                <p class="app-subtitle">Vue d'ensemble des ventes, stocks et approvisionnements.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('sales.index') }}" wire:navigate class="app-btn-primary">Nouvelle vente</a>
                <a href="{{ route('products.index') }}" wire:navigate class="app-btn-secondary">Ajouter produit</a>
                <a href="{{ route('stocks.index') }}" wire:navigate class="app-btn-secondary">Mouvement stock</a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="app-kpi">
                <div class="app-kpi-label">Ventes du jour</div>
                <div class="app-kpi-value">0</div>
                <div class="app-kpi-trend">+0% vs hier</div>
            </div>
            <div class="app-kpi">
                <div class="app-kpi-label">Chiffre d'affaires</div>
                <div class="app-kpi-value">0.00</div>
                <div class="app-kpi-trend">CA cumule</div>
            </div>
            <div class="app-kpi">
                <div class="app-kpi-label">Articles en stock</div>
                <div class="app-kpi-value">0</div>
                <div class="app-kpi-trend">Stock surveille</div>
            </div>
            <div class="app-kpi">
                <div class="app-kpi-label">Fournisseurs actifs</div>
                <div class="app-kpi-value">0</div>
                <div class="app-kpi-trend">Partenaires suivis</div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="app-card lg:col-span-2">
                <div class="app-card-header">
                    <div>
                        <h3 class="app-card-title">Operations rapides</h3>
                        <p class="app-card-subtitle">Accedez aux actions les plus utilisees.</p>
                    </div>
                </div>
                <div class="app-card-body grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('sales.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="text-sm font-semibold text-slate-900">Enregistrer une vente</div>
                        <div class="mt-2 text-sm text-slate-500">Ajout rapide au panier et facturation.</div>
                    </a>
                    <a href="{{ route('purchases.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="text-sm font-semibold text-slate-900">Reception fournisseur</div>
                        <div class="mt-2 text-sm text-slate-500">Mise a jour des stocks et couts.</div>
                    </a>
                    <a href="{{ route('products.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="text-sm font-semibold text-slate-900">Nouveau produit</div>
                        <div class="mt-2 text-sm text-slate-500">Ajoutez un article au catalogue.</div>
                    </a>
                    <a href="{{ route('stocks.index') }}" wire:navigate class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="text-sm font-semibold text-slate-900">Ajustement stock</div>
                        <div class="mt-2 text-sm text-slate-500">Corrigez les ecarts rapidement.</div>
                    </a>
                </div>
            </div>

            <div class="app-card">
                <div class="app-card-header">
                    <div>
                        <h3 class="app-card-title">Rappels</h3>
                        <p class="app-card-subtitle">Suivi des points critiques.</p>
                    </div>
                </div>
                <div class="app-card-body space-y-4 text-sm text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-amber-400"></span>
                        Verifiez les produits a faible rotation cette semaine.
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-teal-400"></span>
                        Planifiez les reapprovisionnements prioritaires.
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-sky-400"></span>
                        Suivez les factures clients emises.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
