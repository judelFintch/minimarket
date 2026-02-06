<?php

namespace App\Livewire\Stocks;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $productId = null;
    public string $type = 'in';
    public int $quantity = 0;
    public ?string $reason = null;
    public ?string $occurred_at = null;
    public string $search = '';

    protected function rules(): array
    {
        return [
            'productId' => ['required', 'exists:products,id'],
            'type' => ['required', Rule::in(['in', 'out', 'adjustment'])],
            'quantity' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset(['productId', 'type', 'quantity', 'reason', 'occurred_at']);
        $this->type = 'in';
        $this->quantity = 0;
    }

    public function saveMovement(): void
    {
        $this->authorizeAccess();
        $validated = $this->validate();

        if ($validated['type'] !== 'adjustment' && $validated['quantity'] <= 0) {
            $this->addError('quantity', 'La quantite doit etre superieure a zero.');
            return;
        }

        if ($validated['type'] === 'adjustment' && $validated['quantity'] === 0) {
            $this->addError('quantity', 'La quantite ne peut pas etre zero.');
            return;
        }

        DB::transaction(function () use ($validated) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $validated['productId']],
                ['quantity' => 0]
            );

            $delta = match ($validated['type']) {
                'in' => $validated['quantity'],
                'out' => -$validated['quantity'],
                default => $validated['quantity'],
            };

            $newQuantity = $stock->quantity + $delta;

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock insuffisant pour cette sortie.',
                ]);
            }

            $stock->update(['quantity' => $newQuantity]);

            StockMovement::create([
                'product_id' => $validated['productId'],
                'type' => $validated['type'],
                'quantity' => $validated['quantity'],
                'reason' => $validated['reason'],
                'occurred_at' => $validated['occurred_at'],
            ]);
        });

        $this->resetForm();
    }

    public function render()
    {
        $this->authorizeAccess();
        $products = Product::query()
            ->orderBy('name')
            ->get();

        $movements = StockMovement::query()
            ->with('product')
            ->when($this->search !== '', function ($query) {
                $query->whereHas('product', function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.stocks.index', [
            'products' => $products,
            'movements' => $movements,
        ])->layout('layouts.app');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
