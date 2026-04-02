<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\ArchivedIndex;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArchivedVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_products_page_hides_archived_products(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $activeProduct = Product::factory()->create([
            'name' => 'Produit Actif Visible',
        ]);
        $archivedProduct = Product::factory()->create([
            'name' => 'Produit Archive Invisible',
            'archived_at' => now(),
        ]);

        Stock::factory()->create([
            'product_id' => $activeProduct->id,
            'quantity' => 12,
        ]);
        Stock::factory()->create([
            'product_id' => $archivedProduct->id,
            'quantity' => 8,
        ]);

        $response = $this->actingAs($admin)->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Produit Actif Visible');
        $response->assertDontSee('Produit Archive Invisible');
        $response->assertSee('Produits archives');
    }

    public function test_archived_products_page_shows_only_archived_products(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $activeProduct = Product::factory()->create([
            'name' => 'Produit Actif Masque',
        ]);
        $archivedProduct = Product::factory()->create([
            'name' => 'Produit Archive Visible',
            'archived_at' => now(),
        ]);

        Stock::factory()->create([
            'product_id' => $activeProduct->id,
            'quantity' => 4,
        ]);
        Stock::factory()->create([
            'product_id' => $archivedProduct->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($admin)->get(route('products.archived'));

        $response->assertOk();
        $response->assertSee('Produit Archive Visible');
        $response->assertDontSee('Produit Actif Masque');
    }

    public function test_archived_products_can_be_restored_from_archived_products_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $archivedProduct = Product::factory()->create([
            'archived_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(ArchivedIndex::class)
            ->call('restoreProduct', $archivedProduct->id);

        $this->assertDatabaseHas('products', [
            'id' => $archivedProduct->id,
            'archived_at' => null,
        ]);
    }

    public function test_sales_page_hides_archived_products(): void
    {
        $user = User::factory()->create([
            'role' => 'vendeur',
        ]);

        $activeProduct = Product::factory()->create([
            'name' => 'Produit Vente Visible',
        ]);
        $archivedProduct = Product::factory()->create([
            'name' => 'Produit Vente Archive',
            'archived_at' => now(),
        ]);

        Stock::factory()->create([
            'product_id' => $activeProduct->id,
            'quantity' => 10,
        ]);
        Stock::factory()->create([
            'product_id' => $archivedProduct->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($user)->get(route('sales.index'));

        $response->assertOk();
        $response->assertSee('Produit Vente Visible');
        $response->assertDontSee('Produit Vente Archive');
    }
}
