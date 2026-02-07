<?php

use App\Livewire\Actions\Logout;
use App\Models\Product;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $user = auth()->user();
    $role = $user?->role ?? 'vendeur';
    $lowStockCount = 0;
    if ($role !== 'vendeur_simple') {
        $lowStockCount = Product::query()
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.archived_at')
            ->whereRaw('COALESCE(stocks.quantity, 0) <= products.min_stock')
            ->count();
    }
    $navSections = [
        [
            'title' => 'Operations',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
                ['label' => 'Ventes', 'route' => 'sales.index'],
                ['label' => 'Historique', 'route' => 'sales.history'],
            ],
        ],
    ];

    if ($role !== 'vendeur_simple') {
        $navSections[] = [
            'title' => 'Stock & Achats',
            'items' => [
                ['label' => 'Stock', 'route' => 'stocks.index'],
                ['label' => 'Alertes stock', 'route' => 'stocks.alerts', 'badge' => $lowStockCount],
                ['label' => 'Sorties stock', 'route' => 'stock-outs.index'],
                ['label' => 'Produits', 'route' => 'products.index'],
                ['label' => 'Categories', 'route' => 'categories.index'],
                ['label' => 'Fournisseurs', 'route' => 'suppliers.index'],
                ['label' => 'Achats', 'route' => 'purchases.index'],
            ],
        ];

        $navSections[] = [
            'title' => 'Depenses',
            'items' => [
                ['label' => 'Categories depenses', 'route' => 'expense-categories.index'],
                ['label' => 'Depenses', 'route' => 'expenses.index'],
            ],
        ];

        $navSections[] = [
            'title' => 'Rapports',
            'items' => [
                ['label' => 'Rapports ventes', 'route' => 'reports.sales'],
                ['label' => 'Solde caisse', 'route' => 'reports.cashflow'],
            ],
        ];
    }

    if ($role === 'admin') {
        $navSections[] = [
            'title' => 'Administration',
            'items' => [
                ['label' => 'Utilisateurs', 'route' => 'users.index'],
            ],
        ];
    }

    $navItems = collect($navSections)
        ->pluck('items')
        ->flatten(1)
        ->all();
@endphp

<div>
    <div class="app-topbar">
        <a href="{{ route('dashboard') }}" wire:navigate class="app-topbar-title">
            {{ config('app.name', 'miniMaket') }}
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('profile') }}" wire:navigate class="app-btn-ghost">Profil</a>
            <button wire:click="logout" class="app-btn-ghost">Deconnexion</button>
        </div>
    </div>

    <div class="border-b border-slate-200/70 bg-white/80 px-4 py-3 backdrop-blur lg:hidden">
        <div class="flex gap-2 overflow-x-auto text-sm font-semibold text-slate-600">
            @foreach ($navItems as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                    wire:navigate
                    class="whitespace-nowrap rounded-full px-4 py-2 {{ $active ? 'bg-teal-50 text-teal-700 ring-1 ring-teal-100' : 'bg-slate-100 text-slate-600' }}">
                    {{ $item['label'] }}
                    @if (! empty($item['badge']))
                        <span class="ml-2 rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">{{ $item['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <aside class="app-sidebar">
        <div class="app-sidebar-header">
            <x-application-logo class="h-12 w-12 fill-current text-teal-600" />
            <div>
                <div class="app-sidebar-title">{{ config('app.name', 'miniMaket') }}</div>
                <div class="app-sidebar-subtitle">Gestion magasin</div>
            </div>
        </div>

        <nav class="app-sidebar-nav">
            @foreach ($navSections as $section)
                <div class="app-sidebar-section">
                    <div class="app-sidebar-section-title">{{ $section['title'] }}</div>
                    <div class="space-y-2">
                        @foreach ($section['items'] as $item)
                            @php $active = request()->routeIs($item['route']); @endphp
                            <a href="{{ route($item['route']) }}"
                                wire:navigate
                                class="app-sidebar-link {{ $active ? 'app-sidebar-link-active' : '' }}">
                                <span class="app-sidebar-dot {{ $active ? 'app-sidebar-dot-active' : '' }}"></span>
                                <span class="flex-1">{{ $item['label'] }}</span>
                                @if (! empty($item['badge']))
                                    <span class="app-nav-badge">{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-slate-200/70 px-4 py-4">
            <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                <div>
                    <div class="text-xs text-slate-500">Connecte</div>
                    <div class="font-semibold text-slate-700">{{ auth()->user()->name ?? 'Utilisateur' }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('profile') }}" wire:navigate class="app-btn-ghost">Profil</a>
                    <button wire:click="logout" class="app-btn-ghost text-rose-600 hover:text-rose-700">Quitter</button>
                </div>
            </div>
        </div>
    </aside>
</div>
