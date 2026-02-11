<?php

namespace Tests\Feature;

use App\Livewire\Products\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_product_and_stock_from_csv(): void
    {
        $user = User::factory()->create();
        $csv = implode("\n", [
            'name,sku,barcode,unit,cost_price,sale_price,currency,stock_quantity,min_stock,reorder_qty,category',
            'Coca,SKU-001,BAR-001,bottle,1000,1500,CDF,12,2,6,Boissons',
        ]);

        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('importFile', $file)
            ->call('importProducts')
            ->assertSet('importedCount', 1)
            ->assertSet('skippedCount', 0);

        $category = Category::query()->where('name', 'Boissons')->first();
        $this->assertNotNull($category);

        $product = Product::query()->where('sku', 'SKU-001')->first();
        $this->assertNotNull($product);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Coca',
            'barcode' => 'BAR-001',
            'currency' => 'CDF',
            'min_stock' => 2,
            'reorder_qty' => 6,
        ]);

        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'quantity' => 12,
        ]);
    }

    public function test_import_rolls_back_when_row_has_errors(): void
    {
        $user = User::factory()->create();
        $csv = implode("\n", [
            'name,sku,barcode,unit,cost_price,sale_price,currency,stock_quantity,min_stock,reorder_qty,category',
            'Produit OK,SKU-OK,BAR-OK,pcs,100,150,CDF,5,1,2,Food',
            'Produit KO,SKU-KO,BAR-KO,pcs,abc,150,CDF,5,1,2,Food',
        ]);

        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('importFile', $file)
            ->call('importProducts')
            ->assertSet('importedCount', 0)
            ->assertSet('skippedCount', 1)
            ->assertSee('Import annule: des erreurs ont ete detectees.');

        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-OK',
        ]);
        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-KO',
        ]);
    }
}
