<?php

namespace Tests\Feature;

use App\Livewire\GlobalSearch;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_lists_results(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['name' => 'Café Royal']);
        $sale = Sale::factory()->create(['reference' => 'SALE-TEST-001', 'user_id' => $user->id]);
        $purchase = Purchase::factory()->create(['reference' => 'PUR-TEST-001', 'user_id' => $user->id]);
        $expense = Expense::factory()->create(['title' => 'Loyer boutique']);
        $supplier = Supplier::factory()->create(['name' => 'Fournisseur ABC']);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class)
            ->set('query', 'TEST')
            ->assertSee($sale->reference)
            ->assertSee($purchase->reference);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class)
            ->set('query', 'Café')
            ->assertSee($product->name);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class)
            ->set('query', 'Fournisseur')
            ->assertSee($supplier->name);

        Livewire::actingAs($user)
            ->test(GlobalSearch::class)
            ->set('query', 'Loyer')
            ->assertSee($expense->title);
    }
}
