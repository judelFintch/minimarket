<?php

namespace App\Livewire\Sales;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?string $customer_name = null;
    public ?string $sold_at = null;
    public array $items = [];
    public string $search = '';
    public ?string $date_from = null;
    public ?string $date_to = null;
    public string $status_filter = '';
    public string $barcodeInput = '';
    public string $productSearch = '';
    public float $discountRate = 0;
    public float $taxRate = 0;
    public string $catalogSearch = '';
    public int $catalogCategory = 0;
    public string $catalogStock = '';
    public int $catalogPerPage = 20;

    protected function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:255'],
            'sold_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discountRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'taxRate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function mount(): void
    {
        $this->sold_at = now()->format('Y-m-d');
        $this->items = [
            [
                'product_id' => null,
                'quantity' => 1,
                'unit_price' => null,
                'discount_rate' => 0,
            ],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingProductSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCatalogSearch(): void
    {
        $this->resetPage();
        $this->catalogPerPage = 20;
    }

    public function updatingCatalogCategory(): void
    {
        $this->resetPage();
        $this->catalogPerPage = 20;
    }

    public function updatingCatalogStock(): void
    {
        $this->resetPage();
        $this->catalogPerPage = 20;
    }

    public function loadMoreCatalog(): void
    {
        $this->catalogPerPage += 20;
    }

    public function toggleFavorite(int $productId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $user->favoriteProducts()->toggle($productId);
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'quantity' => 1,
            'unit_price' => null,
            'discount_rate' => 0,
        ];
    }

    public function addProduct(int $productId): void
    {
        foreach ($this->items as $index => $item) {
            if ((int) ($item['product_id'] ?? 0) === $productId) {
                $this->items[$index]['quantity'] = ((int) ($item['quantity'] ?? 0)) + 1;
                if (! $this->items[$index]['unit_price']) {
                    $this->items[$index]['unit_price'] = Product::query()
                        ->whereKey($productId)
                        ->value('sale_price');
                }
                return;
            }
        }

        $product = Product::query()->select(['id', 'sale_price', 'promo_price'])->find($productId);
        $price = $product && $product->promo_price !== null ? (float) $product->promo_price : (float) ($product?->sale_price ?? 0);
        $this->items[] = [
            'product_id' => $productId,
            'quantity' => 1,
            'unit_price' => $price ?? 0,
            'discount_rate' => 0,
        ];
    }

    public function updatedBarcodeInput($value): void
    {
        $barcode = trim((string) $value);

        if ($barcode === '') {
            return;
        }

        $productId = Product::query()
            ->where('barcode', $barcode)
            ->value('id');

        if ($productId) {
            $this->addProduct((int) $productId);
            $this->barcodeInput = '';
        }
    }

    public function updatedItems($value, $name): void
    {
        if (! str_ends_with($name, '.product_id')) {
            return;
        }

        $index = (int) explode('.', $name)[0];

        if (! $value) {
            $this->items[$index]['unit_price'] = null;
            return;
        }

        $product = Product::query()->select(['id', 'sale_price', 'promo_price'])->find($value);
        $price = $product && $product->promo_price !== null ? (float) $product->promo_price : (float) ($product?->sale_price ?? 0);
        $this->items[$index]['unit_price'] = $price;
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function resetForm(): void
    {
        $this->reset(['customer_name', 'sold_at', 'items']);
        $this->discountRate = 0;
        $this->taxRate = 0;
        $this->sold_at = now()->format('Y-m-d');
        $this->items = [
            [
                'product_id' => null,
                'quantity' => 1,
                'unit_price' => null,
                'discount_rate' => 0,
            ],
        ];
    }

    private function normalizeItemsWithPrices(array $items): array
    {
        $productIds = collect($items)->pluck('product_id')->filter()->unique()->values();
        $prices = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'sale_price', 'promo_price'])
            ->mapWithKeys(function ($product) {
                $price = $product->promo_price !== null ? (float) $product->promo_price : (float) $product->sale_price;
                return [$product->id => $price];
            });

        return collect($items)->map(function ($item) use ($prices) {
            $price = (float) ($prices[$item['product_id']] ?? 0);
            $item['unit_price'] = $price;
            $item['discount_rate'] = (float) ($item['discount_rate'] ?? 0);
            return $item;
        })->all();
    }

    private function calculateTotals(array $items, float $discountRate, float $taxRate): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $lineBase = $quantity * $price;
            $lineDiscountRate = (float) ($item['discount_rate'] ?? 0);
            $lineDiscountAmount = $lineBase * ($lineDiscountRate / 100);
            $lineTotal = $lineBase - $lineDiscountAmount;
            $subtotal += $lineTotal;
        }

        $discountAmount = $subtotal * ($discountRate / 100);
        $taxable = $subtotal - $discountAmount;
        $taxAmount = $taxable * ($taxRate / 100);
        $total = $taxable + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discountAmount' => round($discountAmount, 2),
            'taxAmount' => round($taxAmount, 2),
            'total' => round($total, 2),
        ];
    }

    public function saveSale(): void
    {
        $validated = $this->validate();
        $validated['items'] = $this->normalizeItemsWithPrices($validated['items']);

        $totalsByProduct = [];
        foreach ($validated['items'] as $item) {
            $totalsByProduct[$item['product_id']] = ($totalsByProduct[$item['product_id']] ?? 0) + $item['quantity'];
        }

        DB::transaction(function () use ($validated, $totalsByProduct) {
            $stocks = Stock::query()
                ->whereIn('product_id', array_keys($totalsByProduct))
                ->get()
                ->keyBy('product_id');

            foreach ($totalsByProduct as $productId => $requiredQty) {
                $currentQty = $stocks->get($productId)?->quantity ?? 0;
                if ($currentQty < $requiredQty) {
                    throw ValidationException::withMessages([
                        'items' => 'Stock insuffisant pour au moins un produit.',
                    ]);
                }
            }

            $totals = $this->calculateTotals($validated['items'], (float) $this->discountRate, (float) $this->taxRate);

            $sale = Sale::create([
                'reference' => 'SALE-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'customer_name' => $validated['customer_name'],
                'total_amount' => $totals['total'],
                'subtotal_amount' => $totals['subtotal'],
                'discount_rate' => $this->discountRate,
                'discount_amount' => $totals['discountAmount'],
                'tax_rate' => $this->taxRate,
                'tax_amount' => $totals['taxAmount'],
                'status' => 'paid',
                'sold_at' => $validated['sold_at'],
            ]);

            foreach ($validated['items'] as $item) {
                $lineBase = $item['quantity'] * $item['unit_price'];
                $lineDiscountAmount = $lineBase * ((float) ($item['discount_rate'] ?? 0) / 100);
                $lineTotal = $lineBase - $lineDiscountAmount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $lineDiscountAmount,
                    'line_total' => $lineTotal,
                ]);
            }

            foreach ($totalsByProduct as $productId => $requiredQty) {
                $stock = $stocks->get($productId) ?? Stock::create([
                    'product_id' => $productId,
                    'quantity' => 0,
                ]);

                $stock->update([
                    'quantity' => $stock->quantity - $requiredQty,
                ]);

                StockMovement::create([
                    'product_id' => $productId,
                    'type' => 'out',
                    'quantity' => $requiredQty,
                    'reason' => 'Vente ' . $sale->reference,
                    'occurred_at' => $validated['sold_at'],
                ]);
            }

            Invoice::create([
                'sale_id' => $sale->id,
                'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'total_amount' => $totals['total'],
                'status' => 'paid',
                'issued_at' => $validated['sold_at'],
                'due_at' => $validated['sold_at'],
            ]);
        });

        $this->resetForm();
    }

    public function savePending(): void
    {
        $validated = $this->validate();
        $validated['items'] = $this->normalizeItemsWithPrices($validated['items']);

        DB::transaction(function () use ($validated) {
            $totals = $this->calculateTotals($validated['items'], (float) $this->discountRate, (float) $this->taxRate);

            $sale = Sale::create([
                'reference' => 'SALE-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'customer_name' => $validated['customer_name'],
                'total_amount' => $totals['total'],
                'subtotal_amount' => $totals['subtotal'],
                'discount_rate' => $this->discountRate,
                'discount_amount' => $totals['discountAmount'],
                'tax_rate' => $this->taxRate,
                'tax_amount' => $totals['taxAmount'],
                'status' => 'pending',
                'sold_at' => $validated['sold_at'],
            ]);

            foreach ($validated['items'] as $item) {
                $lineBase = $item['quantity'] * $item['unit_price'];
                $lineDiscountAmount = $lineBase * ((float) ($item['discount_rate'] ?? 0) / 100);
                $lineTotal = $lineBase - $lineDiscountAmount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $lineDiscountAmount,
                    'line_total' => $lineTotal,
                ]);
            }
        });

        $this->resetForm();
    }

    public function finalizeSale(int $saleId): void
    {
        $sale = Sale::query()->with('items')->findOrFail($saleId);

        if ($sale->status === 'paid') {
            return;
        }

        $totalsByProduct = $sale->items
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'))
            ->toArray();

        DB::transaction(function () use ($sale, $totalsByProduct) {
            $stocks = Stock::query()
                ->whereIn('product_id', array_keys($totalsByProduct))
                ->get()
                ->keyBy('product_id');

            foreach ($totalsByProduct as $productId => $requiredQty) {
                $currentQty = $stocks->get($productId)?->quantity ?? 0;
                if ($currentQty < $requiredQty) {
                    throw ValidationException::withMessages([
                        'items' => 'Stock insuffisant pour finaliser cette vente.',
                    ]);
                }
            }

            foreach ($totalsByProduct as $productId => $requiredQty) {
                $stock = $stocks->get($productId) ?? Stock::create([
                    'product_id' => $productId,
                    'quantity' => 0,
                ]);

                $stock->update([
                    'quantity' => $stock->quantity - $requiredQty,
                ]);

                StockMovement::create([
                    'product_id' => $productId,
                    'type' => 'out',
                    'quantity' => $requiredQty,
                    'reason' => 'Vente ' . $sale->reference,
                    'occurred_at' => $sale->sold_at ?? now(),
                ]);
            }

            $totals = $this->calculateTotals($sale->items->toArray(), (float) $sale->discount_rate, (float) $sale->tax_rate);

            $sale->update([
                'status' => 'paid',
                'total_amount' => $totals['total'],
                'subtotal_amount' => $totals['subtotal'],
                'discount_amount' => $totals['discountAmount'],
                'tax_amount' => $totals['taxAmount'],
                'sold_at' => $sale->sold_at ?? now(),
            ]);

            if (! $sale->invoice) {
                Invoice::create([
                    'sale_id' => $sale->id,
                    'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'total_amount' => $totals['total'],
                    'status' => 'paid',
                    'issued_at' => $sale->sold_at ?? now(),
                    'due_at' => $sale->sold_at ?? now(),
                ]);
            }
        });
    }

    public function render()
    {
        $products = Product::query()
            ->with(['stock', 'category'])
            ->orderBy('name')
            ->get();

        $filteredProducts = collect();
        if ($this->productSearch !== '') {
            $filteredProducts = Product::query()
                ->with('stock')
                ->where('name', 'like', '%' . $this->productSearch . '%')
                ->orderBy('name')
                ->limit(6)
                ->get();
        }

        $catalogQuery = Product::query()
            ->with(['stock', 'category'])
            ->when($this->catalogSearch !== '', function ($query) {
                $query->where('name', 'like', '%' . $this->catalogSearch . '%');
            })
            ->when($this->catalogCategory > 0, function ($query) {
                $query->where('category_id', $this->catalogCategory);
            })
            ->when($this->catalogStock === 'low', function ($query) {
                $query->whereHas('stock', function ($stockQuery) {
                    $stockQuery->where('quantity', '<=', 5);
                });
            })
            ->when($this->catalogStock === 'out', function ($query) {
                $query->whereHas('stock', function ($stockQuery) {
                    $stockQuery->where('quantity', '<=', 0);
                });
            })
            ->orderBy('name');

        $catalogTotal = (clone $catalogQuery)->count();
        $catalogProducts = (clone $catalogQuery)
            ->limit($this->catalogPerPage)
            ->get();

        $categories = \App\Models\Category::query()
            ->orderBy('name')
            ->get();

        $favoriteIds = auth()->user()?->favoriteProducts()->pluck('products.id')->all() ?? [];
        $favoriteProducts = auth()->user()
            ? auth()->user()->favoriteProducts()->with(['stock', 'category'])->limit(8)->get()
            : collect();

        $frequentProductIds = SaleItem::query()
            ->select('product_id', DB::raw('sum(quantity) as total_qty'))
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'paid')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(6)
            ->pluck('product_id')
            ->all();

        $frequentProducts = collect();
        if (! empty($frequentProductIds)) {
            $ids = implode(',', $frequentProductIds);
            $frequentProducts = Product::query()
                ->with(['stock', 'category'])
                ->whereIn('id', $frequentProductIds)
                ->orderByRaw("FIELD(id, {$ids})")
                ->get();
        }

        $todaySalesQuery = Sale::query()->whereDate('sold_at', now()->toDateString())->where('status', 'paid');
        $todayCount = (int) $todaySalesQuery->count();
        $todayTotal = (float) $todaySalesQuery->sum('total_amount');
        $avgTicket = $todayCount > 0 ? $todayTotal / $todayCount : 0;
        $pendingCount = (int) Sale::query()->where('status', 'pending')->count();

        $sales = Sale::query()
            ->with(['invoice'])
            ->withCount('items')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('reference', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->date_from, function ($query) {
                $query->whereDate('sold_at', '>=', $this->date_from);
            })
            ->when($this->date_to, function ($query) {
                $query->whereDate('sold_at', '<=', $this->date_to);
            })
            ->when($this->status_filter !== '', function ($query) {
                $query->where('status', $this->status_filter);
            })
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(10);

        $totals = $this->calculateTotals($this->items, (float) $this->discountRate, (float) $this->taxRate);

        return view('livewire.sales.index', [
            'products' => $products,
            'filteredProducts' => $filteredProducts,
            'catalogProducts' => $catalogProducts,
            'catalogTotal' => $catalogTotal,
            'categories' => $categories,
            'favoriteProducts' => $favoriteProducts,
            'favoriteIds' => $favoriteIds,
            'frequentProducts' => $frequentProducts,
            'sales' => $sales,
            'totals' => $totals,
            'todayCount' => $todayCount,
            'todayTotal' => $todayTotal,
            'avgTicket' => $avgTicket,
            'pendingCount' => $pendingCount,
        ])->layout('layouts.app');
    }
}
