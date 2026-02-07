<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $productId = null;
    public ?int $categoryId = null;
    public string $name = '';
    public ?string $sku = null;
    public ?string $barcode = null;
    public ?string $unit = null;
    public ?float $cost_price = null;
    public ?float $sale_price = null;
    public string $currency = 'CDF';
    public int $stock_quantity = 0;
    public int $min_stock = 0;
    public int $reorder_qty = 0;
    public string $search = '';
    public string $deleteError = '';
    public bool $showArchived = false;

    protected function rules(): array
    {
        return [
            'categoryId' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($this->productId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'barcode')->ignore($this->productId),
            ],
            'unit' => ['nullable', 'string', 'max:50'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['CDF', 'USD', 'EUR'])],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'reorder_qty' => ['required', 'integer', 'min:0'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingShowArchived(): void
    {
        $this->resetPage();
    }

    public function editProduct(int $productId): void
    {
        $this->authorizeAccess();
        $this->deleteError = '';
        $product = Product::query()->with('stock')->findOrFail($productId);

        $this->productId = $product->id;
        $this->categoryId = $product->category_id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->barcode = $product->barcode;
        $this->unit = $product->unit;
        $this->cost_price = $product->cost_price;
        $this->sale_price = $product->sale_price;
        $this->currency = $product->currency ?? 'CDF';
        $this->stock_quantity = $product->stock?->quantity ?? 0;
        $this->min_stock = $product->min_stock ?? 0;
        $this->reorder_qty = $product->reorder_qty ?? 0;
    }

    public function resetForm(): void
    {
        $this->deleteError = '';
        $this->reset([
            'productId',
            'categoryId',
            'name',
            'sku',
            'barcode',
            'unit',
            'cost_price',
            'sale_price',
            'currency',
            'stock_quantity',
            'min_stock',
            'reorder_qty',
        ]);
    }

    public function saveProduct(): void
    {
        $this->authorizeAccess();
        $this->deleteError = '';
        $validated = $this->validate();

        $product = Product::updateOrCreate(
            ['id' => $this->productId],
            [
                'category_id' => $validated['categoryId'],
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'barcode' => $validated['barcode'],
                'unit' => $validated['unit'],
                'cost_price' => $validated['cost_price'],
                'sale_price' => $validated['sale_price'],
                'currency' => $validated['currency'],
                'min_stock' => $validated['min_stock'],
                'reorder_qty' => $validated['reorder_qty'],
            ]
        );

        Stock::updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $validated['stock_quantity']]
        );

        $this->resetForm();
    }

    public function deleteProduct(int $productId): void
    {
        $this->authorizeAccess();
        $product = Product::query()->findOrFail($productId);

        $product->update([
            'archived_at' => Carbon::now(),
        ]);
        $this->deleteError = '';

        $this->resetForm();
    }

    public function restoreProduct(int $productId): void
    {
        $this->authorizeAccess();
        Product::query()
            ->whereNotNull('archived_at')
            ->findOrFail($productId)
            ->update(['archived_at' => null]);
    }

    public function render()
    {
        $this->authorizeAccess();
        $products = Product::query()
            ->with(['category', 'stock'])
            ->whereNull('archived_at')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        $archivedProducts = collect();
        if ($this->showArchived) {
            $archivedProducts = Product::query()
                ->with(['category', 'stock'])
                ->whereNotNull('archived_at')
                ->when($this->search !== '', function ($query) {
                    $query->where(function ($subQuery) {
                        $subQuery->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('sku', 'like', '%' . $this->search . '%')
                            ->orWhere('barcode', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('name')
                ->paginate(10, pageName: 'archived');
        }

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('livewire.products.index', [
            'products' => $products,
            'archivedProducts' => $archivedProducts,
            'categories' => $categories,
        ])->layout('layouts.app');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
