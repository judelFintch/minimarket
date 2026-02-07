<?php

namespace App\Livewire;

use App\Models\ExpensePayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user?->isAdmin() ?? false;

        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $salesTodayQuery = Sale::query()
            ->where('status', 'paid')
            ->whereDate('sold_at', $today);
        if (! $isAdmin) {
            $salesTodayQuery->where('user_id', auth()->id());
        }

        $salesTodayCount = $salesTodayQuery->count();

        $revenueByCurrency = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'paid')
            ->whereDate('sales.sold_at', '>=', $monthStart)
            ->when(! $isAdmin, fn ($q) => $q->where('sales.user_id', auth()->id()))
            ->selectRaw("COALESCE(products.currency, 'CDF') as currency")
            ->selectRaw('SUM(sale_items.line_total) as total')
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();

        $expenseByCurrency = ExpensePayment::query()
            ->join('expenses', 'expenses.id', '=', 'expense_payments.expense_id')
            ->whereDate('expense_payments.paid_at', '>=', $monthStart)
            ->selectRaw("COALESCE(expenses.currency, 'CDF') as currency")
            ->selectRaw('SUM(expense_payments.amount) as total')
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();

        $netByCurrency = [];
        foreach ($revenueByCurrency as $row) {
            $netByCurrency[$row->currency] = [
                'currency' => $row->currency,
                'income' => (float) $row->total,
                'expense' => 0,
                'net' => (float) $row->total,
            ];
        }

        foreach ($expenseByCurrency as $row) {
            $existing = $netByCurrency[$row->currency] ?? [
                'currency' => $row->currency,
                'income' => 0,
                'expense' => 0,
                'net' => 0,
            ];
            $existing['expense'] = (float) $row->total;
            $existing['net'] = round($existing['income'] - $existing['expense'], 2);
            $netByCurrency[$row->currency] = $existing;
        }

        $lowStockCount = Product::query()
            ->leftJoin('stocks', 'stocks.product_id', '=', 'products.id')
            ->whereNull('products.archived_at')
            ->whereRaw('COALESCE(stocks.quantity, 0) <= products.min_stock')
            ->count();

        $stockCount = Stock::query()->sum('quantity');
        $suppliersCount = Supplier::query()->count();

        $recentSales = Sale::query()
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', auth()->id()))
            ->orderByDesc('sold_at')
            ->limit(5)
            ->get();

        $recentExpenses = DB::table('expenses')
            ->orderByDesc('incurred_at')
            ->limit(5)
            ->get();

        return view('livewire.dashboard', [
            'salesTodayCount' => $salesTodayCount,
            'revenueByCurrency' => $revenueByCurrency,
            'netByCurrency' => collect($netByCurrency)->values(),
            'lowStockCount' => $lowStockCount,
            'stockCount' => $stockCount,
            'suppliersCount' => $suppliersCount,
            'recentSales' => $recentSales,
            'recentExpenses' => $recentExpenses,
        ])->layout('layouts.app');
    }
}
