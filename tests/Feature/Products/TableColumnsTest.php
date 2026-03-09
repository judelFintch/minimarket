<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_table_shows_extended_columns(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $product = Product::factory()->create([
            'barcode' => 'BAR-001',
            'unit' => 'piece',
            'cost_price' => 100,
            'sale_price' => 150,
            'currency' => 'CDF',
        ]);

        Stock::create([
            'product_id' => $product->id,
            'quantity' => 12,
        ]);

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Code-barres');
        $response->assertSee('Unite');
        $response->assertSee('Prix achat');
        $response->assertSee('MAJ');
        $response->assertSee('BAR-001');
    }
}
