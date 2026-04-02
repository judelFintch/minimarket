<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\Index;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_are_exported_as_excel_file(): void
    {
        Excel::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Product::factory()->create();

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('exportProducts');

        Excel::assertDownloaded('products.xlsx');
    }

    public function test_product_template_is_downloaded_as_excel_file(): void
    {
        Excel::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->call('downloadTemplate');

        Excel::assertDownloaded('products-template.xlsx');
    }
}
