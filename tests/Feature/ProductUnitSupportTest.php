<?php

namespace Tests\Feature;

use App\Livewire\Products\Index;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductUnitSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created_with_kilogram_unit_and_fractional_stock(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('name', 'Poisson frais')
            ->set('unit', 'kg')
            ->set('sale_price', 18000)
            ->set('cost_price', 12000)
            ->set('currency', 'CDF')
            ->set('stock_quantity', 8.5)
            ->set('min_stock', 2)
            ->set('reorder_qty', 5)
            ->call('saveProduct');

        $product = Product::query()->where('name', 'Poisson frais')->firstOrFail();

        $this->assertSame('kg', $product->unit);
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'quantity' => 8.5,
        ]);
    }
}
