<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Sales extends Component
{
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $this->authorizeAccess();
        $isAdmin = auth()->user()?->isAdmin() ?? false;

        $summaryQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'paid')
            ->when($this->startDate, fn ($q) => $q->whereDate('sales.sold_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('sales.sold_at', '<=', $this->endDate))
            ->when(! $isAdmin, fn ($q) => $q->where('sales.user_id', auth()->id()))
            ->selectRaw("COALESCE(products.currency, 'CDF') as currency")
            ->selectRaw('SUM(sale_items.line_total) as revenue')
            ->selectRaw('SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as cost')
            ->selectRaw('SUM(sale_items.line_total) - SUM(sale_items.quantity * COALESCE(products.cost_price, 0)) as profit')
            ->groupBy('currency')
            ->orderBy('currency');

        $summaryByCurrency = $summaryQuery->get();

        $salesCount = Sale::query()
            ->where('status', 'paid')
            ->when($this->startDate, fn ($q) => $q->whereDate('sold_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('sold_at', '<=', $this->endDate))
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', auth()->id()))
            ->count();

        $itemsCount = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'paid')
            ->when($this->startDate, fn ($q) => $q->whereDate('sales.sold_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('sales.sold_at', '<=', $this->endDate))
            ->when(! $isAdmin, fn ($q) => $q->where('sales.user_id', auth()->id()))
            ->sum('sale_items.quantity');

        return view('livewire.reports.sales', [
            'summaryByCurrency' => $summaryByCurrency,
            'salesCount' => $salesCount,
            'itemsCount' => $itemsCount,
        ])->layout('layouts.app');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
