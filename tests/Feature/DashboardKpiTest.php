<?php

namespace Tests\Feature;

use App\Livewire\Dashboard as DashboardComponent;
use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_kpis_render_data(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['currency' => 'USD']);
        Stock::factory()->create(['product_id' => $product->id, 'quantity' => 10]);
        Supplier::factory()->create();

        $sale = Sale::factory()->create([
            'status' => 'paid',
            'sold_at' => now(),
            'user_id' => $user->id,
        ]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'line_total' => 100,
        ]);

        $expense = Expense::factory()->create([
            'currency' => 'USD',
            'user_id' => $user->id,
            'incurred_at' => now()->format('Y-m-d'),
        ]);
        ExpensePayment::factory()->create([
            'expense_id' => $expense->id,
            'amount' => 30,
            'paid_at' => now()->format('Y-m-d'),
        ]);

        Livewire::actingAs($user)
            ->test(DashboardComponent::class)
            ->assertSee('Solde net (mois)')
            ->assertSee('100.00')
            ->assertSee('30.00');
    }
}
