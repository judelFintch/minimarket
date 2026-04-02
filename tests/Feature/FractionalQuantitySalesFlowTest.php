<?php

namespace Tests\Feature;

use App\Livewire\Sales\Index;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FractionalQuantitySalesFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_rejects_fractional_quantity_even_for_products_sold_by_weight(): void
    {
        $user = User::factory()->create(['role' => 'vendeur']);
        $product = Product::factory()->create([
            'name' => 'Poisson capitaine',
            'unit' => 'kg',
            'sale_price' => 24000,
            'currency' => 'CDF',
        ]);

        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2.5,
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('sold_at', now()->format('Y-m-d'))
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'quantity' => 0.75,
                    'unit_price' => 24000,
                    'discount_rate' => 0,
                ],
            ])
            ->call('saveSale')
            ->assertHasErrors(['items.0.quantity' => 'integer']);

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'quantity' => 2.5,
        ]);

        $this->assertDatabaseMissing('sale_items', [
            'product_id' => $product->id,
        ]);
    }
}
