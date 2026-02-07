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

    protected $queryString = [
        'search' => ['except' => ''],
    ];

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
        $this->authorizeAccess();
        $this->purchased_at = now()->format('Y-m-d');
        $this->items = [
            [
                'product_id' => null,
                'quantity' => 1,
                'unit_cost' => null,
            ],
        ];
        $this->applySuggestedItems();
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
        $this->authorizeAccess();
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $purchase = Purchase::create([
                'user_id' => auth()->id(),
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
        $this->authorizeAccess();
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->orderBy('name')
            ->get();

        $purchasesQuery = Purchase::query()
            ->with('supplier')
            ->when($this->search !== '', function ($query) {
                $query->where('reference', 'like', '%' . $this->search . '%');
            });

        if (! auth()->user()?->isAdmin()) {
            $purchasesQuery->where('user_id', auth()->id());
        }

        $purchases = $purchasesQuery
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

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }

    private function applySuggestedItems(): void
    {
        $encoded = request()->query('suggest');
        if (! $encoded) {
            return;
        }

        $decoded = base64_decode($encoded, true);
        if (! $decoded) {
            return;
        }

        $payload = json_decode($decoded, true);
        if (! is_array($payload)) {
            return;
        }

        $items = $payload['items'] ?? null;
        if (! is_array($items)) {
            return;
        }

        $productIds = collect($items)
            ->map(fn ($item) => (int) ($item['product_id'] ?? 0))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($productIds)) {
            return;
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get(['id', 'cost_price'])
            ->keyBy('id');

        $hydrated = [];
        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            if (! $productId || ! $products->has($productId)) {
                continue;
            }

            $quantity = (int) ($item['quantity'] ?? 0);
            $quantity = max(1, $quantity);
            $unitCost = $item['unit_cost'] ?? null;
            $unitCost = is_numeric($unitCost) ? (float) $unitCost : (float) ($products[$productId]->cost_price ?? 0);

            $hydrated[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
            ];
        }

        if (empty($hydrated)) {
            return;
        }

        $supplierId = (int) ($payload['supplier_id'] ?? 0);
        $this->supplier_id = $supplierId > 0 ? $supplierId : null;
        $this->items = $hydrated;
    }
}
