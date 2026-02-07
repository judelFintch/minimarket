<?php

namespace App\Livewire\StockOuts;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockOut;
use App\Models\StockOutItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?string $occurred_at = null;
    public ?string $reason = null;
    public ?string $notes = null;
    public array $items = [];
    public string $search = '';

    protected function rules(): array
    {
        return [
            'occurred_at' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->occurred_at = now()->format('Y-m-d');
        $this->items = [
            [
                'product_id' => null,
                'quantity' => 1,
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
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function resetForm(): void
    {
        $this->reset(['occurred_at', 'reason', 'notes', 'items']);
        $this->occurred_at = now()->format('Y-m-d');
        $this->items = [
            [
                'product_id' => null,
                'quantity' => 1,
            ],
        ];
    }

    public function saveStockOut(): void
    {
        $this->authorizeAccess();
        $validated = $this->validate();

        $totalsByProduct = [];
        foreach ($validated['items'] as $item) {
            $productId = (int) $item['product_id'];
            $quantity = (float) $item['quantity'];
            $totalsByProduct[$productId] = ($totalsByProduct[$productId] ?? 0) + $quantity;
        }

        DB::transaction(function () use ($validated, $totalsByProduct) {
            $stocks = Stock::query()
                ->whereIn('product_id', array_keys($totalsByProduct))
                ->get()
                ->keyBy('product_id');

            foreach ($totalsByProduct as $productId => $requiredQty) {
                $currentQty = (float) ($stocks->get($productId)?->quantity ?? 0);
                if ($currentQty < $requiredQty) {
                    throw ValidationException::withMessages([
                        'items' => 'Stock insuffisant pour au moins un produit.',
                    ]);
                }
            }

            $totalQuantity = collect($validated['items'])->sum(fn ($item) => (float) $item['quantity']);

            $stockOut = StockOut::create([
                'user_id' => auth()->id(),
                'reference' => 'OUT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'reason' => $validated['reason'],
                'total_quantity' => $totalQuantity,
                'occurred_at' => $validated['occurred_at'],
                'notes' => $validated['notes'],
            ]);

            $items = [];
            foreach ($validated['items'] as $item) {
                $items[] = [
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (! empty($items)) {
                StockOutItem::insert($items);
            }

            $movements = [];
            foreach ($totalsByProduct as $productId => $requiredQty) {
                $stock = $stocks->get($productId) ?? Stock::create([
                    'product_id' => $productId,
                    'quantity' => 0,
                ]);

                $stock->update([
                    'quantity' => $stock->quantity - $requiredQty,
                ]);

                $movements[] = [
                    'product_id' => $productId,
                    'type' => 'out',
                    'quantity' => $requiredQty,
                    'reason' => 'Sortie ' . $stockOut->reference . ($validated['reason'] ? ' - ' . $validated['reason'] : ''),
                    'occurred_at' => $validated['occurred_at'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (! empty($movements)) {
                StockMovement::insert($movements);
            }
        });

        $this->resetForm();
    }

    public function render()
    {
        $this->authorizeAccess();

        $products = Product::query()
            ->orderBy('name')
            ->get();

        $stockOuts = StockOut::query()
            ->with('items.product')
            ->when($this->search !== '', function ($query) {
                $query->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhere('reason', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.stock-outs.index', [
            'products' => $products,
            'stockOuts' => $stockOuts,
        ])->layout('layouts.app');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
