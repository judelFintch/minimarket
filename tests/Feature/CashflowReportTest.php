<?php

namespace Tests\Feature;

use App\Livewire\Reports\Cashflow as CashflowReport;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CashflowReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashflow_report_summarizes_income_and_expense_by_currency(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['currency' => 'USD']);

        $sale = Sale::factory()->create([
            'status' => 'paid',
            'sold_at' => now()->subDays(2),
            'user_id' => $user->id,
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'line_total' => 100,
        ]);

        $expense = Expense::factory()->create([
            'currency' => 'USD',
            'user_id' => $user->id,
        ]);

        ExpensePayment::factory()->create([
            'expense_id' => $expense->id,
            'amount' => 30,
            'paid_at' => now()->subDays(1)->format('Y-m-d'),
        ]);

        Livewire::actingAs($user)
            ->test(CashflowReport::class)
            ->assertSee('USD')
            ->assertSee('100.00')
            ->assertSee('30.00')
            ->assertSee('70.00');
    }
}
