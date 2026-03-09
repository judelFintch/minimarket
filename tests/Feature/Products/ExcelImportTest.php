<?php

namespace Tests\Feature\Products;

use App\Livewire\Products\Index;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_import_excel_and_update_existing_product(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $product = Product::factory()->create([
            'name' => 'Produit Test',
            'sku' => null,
            'sale_price' => 10,
        ]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'sku', 'sale_price', 'stock_quantity', 'currency'],
            ['Produit Test', null, 25.5, 12, 'CDF'],
        ]);

        $path = sys_get_temp_dir().'/products-import.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $content = file_get_contents($path);
        $file = UploadedFile::fake()->createWithContent('products-import.xlsx', $content);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('importExcelFile', $file)
            ->set('importCreateMissing', false)
            ->set('importMatchByName', true)
            ->call('importProductsExcel')
            ->assertSet('importedCount', 1);

        $product->refresh();
        $this->assertSame(25.5, (float) $product->sale_price);
        $this->assertSame('CDF', $product->currency);
    }

    public function test_non_admin_cannot_see_excel_import_option(): void
    {
        $user = User::factory()->create([
            'role' => 'vendeur',
        ]);

        $response = $this->actingAs($user)->get(route('products.index'));

        $response->assertOk();
        $response->assertDontSee('Import Excel');
    }

    public function test_excel_import_updates_product_by_barcode_even_if_name_matches_another(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $nameMatch = Product::factory()->create([
            'name' => 'Produit X',
            'barcode' => 'BAR-OLD',
            'sale_price' => 10,
        ]);

        $barcodeMatch = Product::factory()->create([
            'name' => 'Produit Y',
            'barcode' => 'BAR-NEW',
            'sale_price' => 15,
        ]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'barcode', 'sale_price', 'stock_quantity', 'currency'],
            ['Produit X', 'BAR-NEW', 99, 5, 'CDF'],
        ]);

        $path = sys_get_temp_dir().'/products-import-barcode.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $content = file_get_contents($path);
        $file = UploadedFile::fake()->createWithContent('products-import-barcode.xlsx', $content);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('importExcelFile', $file)
            ->set('importCreateMissing', false)
            ->set('importMatchByName', true)
            ->call('importProductsExcel')
            ->assertSet('importedCount', 1);

        $barcodeMatch->refresh();
        $nameMatch->refresh();

        $this->assertSame(99.0, (float) $barcodeMatch->sale_price);
        $this->assertSame(10.0, (float) $nameMatch->sale_price);
    }

    public function test_excel_sync_archives_missing_products_and_updates_by_sku(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $keep = Product::factory()->create([
            'sku' => 'SKU-KEEP',
            'barcode' => 'BAR-KEEP',
            'sale_price' => 10,
            'archived_at' => now(),
        ]);

        $archive = Product::factory()->create([
            'sku' => 'SKU-ARCH',
            'sale_price' => 20,
        ]);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['name', 'sku', 'barcode', 'sale_price', 'stock_quantity', 'currency'],
            ['Produit Keep', 'SKU-KEEP', 'BAR-NEW', 50, 8, 'CDF'],
        ]);

        $path = sys_get_temp_dir().'/products-import-sync.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $content = file_get_contents($path);
        $file = UploadedFile::fake()->createWithContent('products-import-sync.xlsx', $content);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('importExcelFile', $file)
            ->set('importSyncExcel', true)
            ->call('importProductsExcel')
            ->assertSet('importedCount', 1);

        $keep->refresh();
        $archive->refresh();

        $this->assertNull($keep->archived_at);
        $this->assertSame('BAR-NEW', $keep->barcode);
        $this->assertSame(50.0, (float) $keep->sale_price);
        $this->assertNotNull($archive->archived_at);
    }
}
