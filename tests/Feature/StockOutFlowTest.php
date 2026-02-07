<?php

namespace Tests\Feature;

use App\Livewire\StockOuts\Index as StockOutsIndex;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockOutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_stock_out_and_decrements_stock(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        Livewire::actingAs($user)
            ->test(StockOutsIndex::class)
            ->set('occurred_at', now()->format('Y-m-d'))
            ->set('reason', 'Perte')
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                ],
            ])
            ->call('saveStockOut');

        $this->assertDatabaseHas('stock_outs', [
            'reason' => 'Perte',
        ]);

        $stockOut = StockOut::query()->firstOrFail();
        $this->assertDatabaseHas('stock_out_items', [
            'stock_out_id' => $stockOut->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'quantity' => 7,
        ]);

        $this->assertSame(1, StockMovement::query()->where('type', 'out')->count());
        $this->assertSame(1, StockOutItem::query()->count());
    }
}
