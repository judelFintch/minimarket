<?php

namespace App\Livewire\Stocks;

use App\Models\Category;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Alerts extends Component
{
    use WithPagination;

    public ?int $categoryId = null;
    public ?int $supplierId = null;
    public ?string $currency = null;
    public string $search = '';
    public array $selected = [];
    public int $windowDays = 30;
    public int $coverageDays = 14;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingSupplierId(): void
    {
        $this->resetPage();
    }

    public function updatingCurrency(): void
    {
        $this->resetPage();
    }

    public function createPurchase(): void
    {
        $this->authorizeAccess();

        $selectedIds = collect($this->selected)
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($selectedIds)) {
            $this->addError('selected', 'Selectionnez au moins un produit.');
            return;
        }

        $products = Product::query()
            ->with('stock')
            ->whereIn('id', $selectedIds)
            ->get()
            ->keyBy('id');

        $salesByProduct = $this->getRecentSalesTotals($selectedIds);

        $items = [];
        foreach ($selectedIds as $productId) {
            $product = $products->get($productId);
            if (! $product) {
                continue;
            }

            $currentStock = (int) ($product->stock?->quantity ?? 0);
            $minStock = (int) ($product->min_stock ?? 0);
            $reorderQty = (int) ($product->reorder_qty ?? 0);
            $totalQty = (int) ($salesByProduct[$productId] ?? 0);

            $suggestedQty = $this->calculateSuggestedQty($currentStock, $minStock, $reorderQty, $totalQty);
            if ($suggestedQty <= 0) {
                continue;
            }

            $items[] = [
                'product_id' => $productId,
                'quantity' => $suggestedQty,
                'unit_cost' => $product->cost_price !== null ? (float) $product->cost_price : 0,
            ];
        }

        if (empty($items)) {
            $this->addError('selected', 'Aucune quantite suggeree pour les produits selectionnes.');
            return;
        }

        $payload = [
            'supplier_id' => $this->supplierId,
            'items' => $items,
        ];

        $this->redirect(route('purchases.index', [
            'suggest' => base64_encode(json_encode($payload)),
        ]), navigate: true);
    }

    public function render()
    {
        $this->authorizeAccess();

        $products = $this->buildLowStockQuery();
        $productIds = $products->pluck('id')->all();
        $salesByProduct = $this->getRecentSalesTotals($productIds);

        $suggested = [];
        foreach ($products as $product) {
            $currentStock = (int) ($product->stock?->quantity ?? 0);
            $minStock = (int) ($product->min_stock ?? 0);
            $reorderQty = (int) ($product->reorder_qty ?? 0);
            $totalQty = (int) ($salesByProduct[$product->id] ?? 0);

            $suggested[$product->id] = $this->calculateSuggestedQty($currentStock, $minStock, $reorderQty, $totalQty);
        }

        $categories = Category::query()->orderBy('name')->get();
        $suppliers = Supplier::query()->orderBy('name')->get();

        return view('livewire.stocks.alerts', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers,
            'suggested' => $suggested,
        ])->layout('layouts.app');
    }

    private function buildLowStockQuery(): LengthAwarePaginator
    {
        $query = Product::query()
            ->select('products.*')
            ->with(['category', 'stock'])
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.archived_at')
            ->whereRaw('COALESCE(stocks.quantity, 0) <= products.min_stock')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('products.name', 'like', '%' . $this->search . '%')
                        ->orWhere('products.sku', 'like', '%' . $this->search . '%')
                        ->orWhere('products.barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryId, fn ($query) => $query->where('products.category_id', $this->categoryId))
            ->when($this->currency, fn ($query) => $query->where('products.currency', $this->currency))
            ->when($this->supplierId, function ($query) {
                $query->whereHas('purchaseItems.purchase', function ($purchaseQuery) {
                    $purchaseQuery->where('supplier_id', $this->supplierId);
                });
            })
            ->orderBy('products.name');

        return $query->paginate(10);
    }

    private function getRecentSalesTotals(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $startDate = Carbon::now()->subDays($this->windowDays)->startOfDay();

        return SaleItem::query()
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as total_qty'))
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'paid')
            ->whereDate('sales.sold_at', '>=', $startDate)
            ->whereIn('sale_items.product_id', $productIds)
            ->groupBy('sale_items.product_id')
            ->pluck('total_qty', 'product_id')
            ->map(fn ($value) => (int) $value)
            ->toArray();
    }

    private function calculateSuggestedQty(int $currentStock, int $minStock, int $reorderQty, int $recentSalesQty): int
    {
        if ($reorderQty > 0) {
            return $reorderQty;
        }

        $avgDaily = $recentSalesQty / max(1, $this->windowDays);
        $targetStock = (int) ceil($avgDaily * $this->coverageDays);
        $needed = max(0, $targetStock - $currentStock);

        if ($needed === 0) {
            $needed = max(0, $minStock - $currentStock);
        }

        return $needed;
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
