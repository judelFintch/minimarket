<?php

namespace App\Livewire\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $supplier_id = null;
    public ?string $purchased_at = null;
    public array $items = [];
    public string $search = '';

    protected function rules(): array
    {
        return [
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'purchased_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function mount(): void
    {
        $this->purchased_at = now()->format('Y-m-d');
        $this->items = [
            [
                'product_id' => null,
                'quantity' => 1,
                'unit_cost' => null,
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
            'unit_cost' => null,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function resetForm(): void
    {
        $this->reset(['supplier_id', 'purchased_at', 'items']);
        $this->purchased_at = now()->format('Y-m-d');
        $this->items = [
            [
                'product_id' => null,
                'quantity' => 1,
                'unit_cost' => null,
            ],
        ];
    }

    public function savePurchase(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'reference' => 'PUR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'total_amount' => 0,
                'status' => 'received',
                'purchased_at' => $validated['purchased_at'],
            ]);

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $lineTotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'line_total' => $lineTotal,
                ]);

                $stock = Stock::firstOrCreate(
                    ['product_id' => $item['product_id']],
                    ['quantity' => 0]
                );

                $stock->update([
                    'quantity' => $stock->quantity + $item['quantity'],
                ]);

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'type' => 'in',
                    'quantity' => $item['quantity'],
                    'reason' => 'Achat ' . $purchase->reference,
                    'occurred_at' => $validated['purchased_at'],
                ]);
            }

            $purchase->update(['total_amount' => $totalAmount]);
        });

        $this->resetForm();
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->orderBy('name')
            ->get();

        $purchases = Purchase::query()
            ->with('supplier')
            ->when($this->search !== '', function ($query) {
                $query->where('reference', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate(10);

        $total = collect($this->items)->sum(function ($item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $cost = (float) ($item['unit_cost'] ?? 0);
            return $quantity * $cost;
        });

        return view('livewire.purchases.index', [
            'suppliers' => $suppliers,
            'products' => $products,
            'purchases' => $purchases,
            'total' => $total,
        ])->layout('layouts.app');
    }
}
