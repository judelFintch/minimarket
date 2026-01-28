<?php

namespace App\Livewire\Sales;

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $date_from = null;
    public ?string $date_to = null;
    public string $status_filter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
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

            $totalAmount = $sale->items->sum('line_total');

            $sale->update([
                'status' => 'paid',
                'total_amount' => $totalAmount,
                'sold_at' => $sale->sold_at ?? now(),
            ]);

            if (! $sale->invoice) {
                Invoice::create([
                    'sale_id' => $sale->id,
                    'invoice_number' => 'INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'total_amount' => $totalAmount,
                    'status' => 'paid',
                    'issued_at' => $sale->sold_at ?? now(),
                    'due_at' => $sale->sold_at ?? now(),
                ]);
            }
        });
    }

    public function render()
    {
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
            ->paginate(12);

        return view('livewire.sales.history', [
            'sales' => $sales,
        ])->layout('layouts.app');
    }
}
