<?php

use App\Livewire\Actions\Logout;
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

<nav class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-semibold text-gray-900">
            miniMaket
        </a>

        <div class="flex items-center gap-2 text-sm font-medium text-gray-700">
            <a href="{{ route('dashboard') }}" wire:navigate class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Dashboard</a>
            <a href="{{ route('sales.index') }}" wire:navigate class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Ventes</a>
            <a href="{{ route('stocks.index') }}" wire:navigate class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Stock</a>
            <a href="{{ route('products.index') }}" wire:navigate class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Produits</a>
            <a href="{{ route('categories.index') }}" wire:navigate class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Categories</a>
            <a href="{{ route('suppliers.index') }}" wire:navigate class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Fournisseurs</a>
            <a href="{{ route('purchases.index') }}" wire:navigate class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Achats</a>
            <a href="{{ route('profile') }}" wire:navigate class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Profil</a>
            <button wire:click="logout" class="rounded-md px-3 py-2 hover:bg-gray-50 hover:text-gray-900">Deconnexion</button>
        </div>
    </div>
</nav>
