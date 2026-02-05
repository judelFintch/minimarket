<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
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
    public int $stock_quantity = 0;
    public string $search = '';
    public string $deleteError = '';

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
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function editProduct(int $productId): void
    {
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
        $this->stock_quantity = $product->stock?->quantity ?? 0;
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
            'stock_quantity',
        ]);
    }

    public function saveProduct(): void
    {
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
        $product = Product::query()->findOrFail($productId);

        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            $this->deleteError = 'Impossible de supprimer: ce produit est lie a des ventes ou des achats.';
            return;
        }

        $product->delete();
        $this->deleteError = '';

        $this->resetForm();
    }

    public function render()
    {
        $products = Product::query()
            ->with(['category', 'stock'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%')
                        ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('livewire.products.index', [
            'products' => $products,
            'categories' => $categories,
        ])->layout('layouts.app');
    }
}
