<?php

namespace Tests\Feature;

use App\Livewire\Sales\Index as SalesIndex;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_finalize_someone_elses_sale(): void
    {
        $user = User::factory()->create(['role' => 'vendeur']);
        $otherUser = User::factory()->create(['role' => 'vendeur']);
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        $sale = Sale::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'pending',
            'sold_at' => now(),
        ]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'line_total' => 20,
        ]);

        try {
            Livewire::actingAs($user)
                ->test(SalesIndex::class)
                ->call('finalizeSale', $sale->id);
            $this->fail('Expected a ModelNotFoundException to be thrown.');
        } catch (ModelNotFoundException $exception) {
            $this->assertTrue(true);
        }

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('invoices', [
            'sale_id' => $sale->id,
        ]);
    }

    public function test_admin_can_finalize_any_sale(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'vendeur']);
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $sale = Sale::factory()->create([
            'user_id' => $seller->id,
            'status' => 'pending',
            'sold_at' => now(),
        ]);
        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 12,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'line_total' => 36,
        ]);

        Livewire::actingAs($admin)
            ->test(SalesIndex::class)
            ->call('finalizeSale', $sale->id);

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('invoices', [
            'sale_id' => $sale->id,
            'status' => 'paid',
        ]);
    }
}
