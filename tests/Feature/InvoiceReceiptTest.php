<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_receipt_page_has_modern_actions_and_keeps_thermal_print_layout(): void
    {
        $user = User::factory()->create(['role' => 'vendeur']);
        $product = Product::factory()->create([
            'name' => 'Savon Premium',
            'sale_price' => 2500,
            'currency' => 'CDF',
        ]);
        $sale = Sale::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'customer_name' => 'Client Comptoir',
            'subtotal_amount' => 5000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 5000,
        ]);

        SaleItem::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 2500,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'line_total' => 5000,
        ]);

        $invoice = Invoice::query()->create([
            'sale_id' => $sale->id,
            'invoice_number' => 'INV-TEST-001',
            'total_amount' => 5000,
            'status' => 'paid',
            'issued_at' => now(),
            'due_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('invoices.receipt', $invoice));

        $response->assertOk();
        $response->assertSee('Retour a la vente');
        $response->assertSee(route('sales.history'), false);
        $response->assertSee('Imprimer le ticket');
        $response->assertSee('Ticket thermique');
        $response->assertSee('58mm auto', false);
    }
}
