<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public function updatedQuery(): void
    {
        $this->dispatch('global-search-updated');
    }

    public function render()
    {
        $results = collect();
        $term = trim($this->query);
        $user = auth()->user();
        $role = $user?->role ?? 'vendeur';
        $canManageStock = $role !== 'vendeur_simple';

        if ($term !== '') {
            if ($canManageStock) {
                $products = Product::query()
                    ->where('name', 'like', '%' . $term . '%')
                    ->orWhere('sku', 'like', '%' . $term . '%')
                    ->orWhere('barcode', 'like', '%' . $term . '%')
                    ->limit(5)
                    ->get(['id', 'name', 'sku']);

                foreach ($products as $product) {
                    $results->push([
                        'type' => 'Produit',
                        'label' => $product->name,
                        'meta' => $product->sku,
                        'url' => route('products.index', ['search' => $product->name]),
                    ]);
                }
            }

            $sales = Sale::query()
                ->where('reference', 'like', '%' . $term . '%')
                ->when($role !== 'admin', fn ($q) => $q->where('user_id', auth()->id()))
                ->limit(4)
                ->get(['id', 'reference']);
            foreach ($sales as $sale) {
                $results->push([
                    'type' => 'Vente',
                    'label' => $sale->reference,
                    'meta' => null,
                    'url' => route('sales.history', ['search' => $sale->reference]),
                ]);
            }

            if ($canManageStock) {
                $purchases = Purchase::query()
                    ->where('reference', 'like', '%' . $term . '%')
                    ->limit(4)
                    ->get(['id', 'reference']);
                foreach ($purchases as $purchase) {
                    $results->push([
                        'type' => 'Achat',
                        'label' => $purchase->reference,
                        'meta' => null,
                        'url' => route('purchases.index', ['search' => $purchase->reference]),
                    ]);
                }
            }

            if ($canManageStock) {
                $expenses = Expense::query()
                    ->where('title', 'like', '%' . $term . '%')
                    ->limit(4)
                    ->get(['id', 'title']);
                foreach ($expenses as $expense) {
                    $results->push([
                        'type' => 'Depense',
                        'label' => $expense->title,
                        'meta' => null,
                        'url' => route('expenses.index', ['search' => $expense->title]),
                    ]);
                }
            }

            if ($canManageStock) {
                $suppliers = Supplier::query()
                    ->where('name', 'like', '%' . $term . '%')
                    ->limit(4)
                    ->get(['id', 'name']);
                foreach ($suppliers as $supplier) {
                    $results->push([
                        'type' => 'Fournisseur',
                        'label' => $supplier->name,
                        'meta' => null,
                        'url' => route('suppliers.index', ['search' => $supplier->name]),
                    ]);
                }
            }
        }

        return view('livewire.global-search', [
            'results' => $results->take(12),
        ]);
    }
}
