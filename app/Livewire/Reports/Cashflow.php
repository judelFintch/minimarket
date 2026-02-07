<?php

namespace App\Livewire\Reports;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Cashflow extends Component
{
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $currency = null;

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

        $incomeQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'paid')
            ->when($this->startDate, fn ($q) => $q->whereDate('sales.sold_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('sales.sold_at', '<=', $this->endDate))
            ->when(! $isAdmin, fn ($q) => $q->where('sales.user_id', auth()->id()))
            ->when($this->currency, fn ($q) => $q->where('products.currency', $this->currency))
            ->selectRaw("COALESCE(products.currency, 'CDF') as currency")
            ->selectRaw('SUM(sale_items.line_total) as income')
            ->groupBy('currency')
            ->orderBy('currency');

        $expenseQuery = DB::table('expense_payments')
            ->join('expenses', 'expenses.id', '=', 'expense_payments.expense_id')
            ->when($this->startDate, fn ($q) => $q->whereDate('expense_payments.paid_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('expense_payments.paid_at', '<=', $this->endDate))
            ->when($this->currency, fn ($q) => $q->where('expenses.currency', $this->currency))
            ->selectRaw("COALESCE(expenses.currency, 'CDF') as currency")
            ->selectRaw('SUM(expense_payments.amount) as expense')
            ->groupBy('currency')
            ->orderBy('currency');

        $incomeByCurrency = $incomeQuery->get();
        $expenseByCurrency = $expenseQuery->get();

        $byCurrency = [];
        foreach ($incomeByCurrency as $row) {
            $byCurrency[$row->currency] = [
                'currency' => $row->currency,
                'income' => (float) $row->income,
                'expense' => 0,
            ];
        }

        foreach ($expenseByCurrency as $row) {
            $byCurrency[$row->currency] = array_merge($byCurrency[$row->currency] ?? [
                'currency' => $row->currency,
                'income' => 0,
                'expense' => 0,
            ], [
                'expense' => (float) $row->expense,
            ]);
        }

        foreach ($byCurrency as $currency => $totals) {
            $byCurrency[$currency]['balance'] = round($totals['income'] - $totals['expense'], 2);
        }

        $summary = collect($byCurrency)->values()->sortBy('currency');

        return view('livewire.reports.cashflow', [
            'summary' => $summary,
        ])->layout('layouts.app');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->role !== 'vendeur_simple', 403);
    }
}
