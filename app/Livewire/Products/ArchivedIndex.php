<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ArchivedIndex extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function restoreProduct(int $productId): void
    {
        $this->authorizeAccess();

        Product::query()
            ->archived()
            ->findOrFail($productId)
            ->update(['archived_at' => null]);
    }

    public function render()
    {
        $this->authorizeAccess();

        $products = Product::query()
            ->archived()
            ->with(['category', 'stock'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('barcode', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('archived_at')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.products.archived-index', [
            'products' => $products,
        ])->layout('layouts.app');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();

        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
