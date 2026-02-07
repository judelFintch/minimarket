<?php

namespace Tests\Feature;

use App\Livewire\Purchases\Index as PurchasesIndex;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchasePrefillTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_prefill_hydrates_items_from_query(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create([
            'cost_price' => 12.34,
        ]);

        $payload = [
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 4,
                    'unit_cost' => null,
                ],
            ],
        ];

        Livewire::actingAs($user)
            ->withQueryParams(['suggest' => base64_encode(json_encode($payload))])
            ->test(PurchasesIndex::class)
            ->assertSet('supplier_id', $supplier->id)
            ->assertSet('items.0.product_id', $product->id)
            ->assertSet('items.0.quantity', 4)
            ->assertSet('items.0.unit_cost', 12.34);
    }
}
