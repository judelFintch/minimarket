<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockAlertsTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_alerts_show_only_relevant_products_and_suggestions(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $lowStockProduct = Product::factory()->create([
            'name' => 'Cafe',
            'min_stock' => 5,
            'reorder_qty' => 0,
        ]);
        Stock::factory()->create([
            'product_id' => $lowStockProduct->id,
            'quantity' => 2,
        ]);

        $reorderProduct = Product::factory()->create([
            'name' => 'Sucre',
            'min_stock' => 3,
            'reorder_qty' => 20,
        ]);
        Stock::factory()->create([
            'product_id' => $reorderProduct->id,
            'quantity' => 1,
        ]);

        $healthyProduct = Product::factory()->create([
            'name' => 'Riz',
            'min_stock' => 3,
        ]);
        Stock::factory()->create([
            'product_id' => $healthyProduct->id,
            'quantity' => 10,
        ]);

        $sale = Sale::factory()->create([
            'status' => 'paid',
            'sold_at' => now()->subDays(3),
        ]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $lowStockProduct->id,
            'quantity' => 30,
            'unit_price' => 10,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'line_total' => 300,
        ]);

        $response = $this->actingAs($user)->get(route('stocks.alerts'));

        $response->assertStatus(200);
        $response->assertSee('Cafe');
        $response->assertSee('Sucre');
        $response->assertDontSee('Riz');
        $response->assertSee('20');
    }
}
