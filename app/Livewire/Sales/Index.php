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

    protected function rules(): array
    {
        return [
            'customer_name' => ['nullable', 'string', 'max:255'],
            'sold_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
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
            ],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'quantity' => 1,
            'unit_price' => null,
        ];
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

        $product = Product::query()->select(['id', 'sale_price'])->find($value);
        $this->items[$index]['unit_price'] = $product?->sale_price ?? 0;
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function resetForm(): void
    {
        $this->reset(['customer_name', 'sold_at', 'items']);
        $this->sold_at = now()->format('Y-m-d');
        $this->items = [
            [
                'product_id' => null,
                'quantity' => 1,
                'unit_price' => null,
            ],
        ];
    }

    public function saveSale(): void
    {
        $validated = $this->validate();

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

            $sale = Sale::create([
                'reference' => 'SALE-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'customer_name' => $validated['customer_name'],
                'total_amount' => 0,
                'status' => 'paid',
                'sold_at' => $validated['sold_at'],
            ]);

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $lineTotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
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

            $sale->update(['total_amount' => $totalAmount]);

            Invoice::create([
                'sale_id' => $sale->id,
                'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'total_amount' => $totalAmount,
                'status' => 'paid',
                'issued_at' => $validated['sold_at'],
                'due_at' => $validated['sold_at'],
            ]);
        });

        $this->resetForm();
    }

    public function render()
    {
        $products = Product::query()
            ->with('stock')
            ->orderBy('name')
            ->get();

        $sales = Sale::query()
            ->with(['invoice'])
            ->withCount('items')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('reference', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(10);

        $total = collect($this->items)->sum(function ($item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            return $quantity * $price;
        });

        return view('livewire.sales.index', [
            'products' => $products,
            'sales' => $sales,
            'total' => $total,
        ])->layout('layouts.app');
    }
}
