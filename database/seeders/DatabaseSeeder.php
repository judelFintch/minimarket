<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $admin = User::firstOrCreate(
            ['email' => 'admin@minimarket.test'],
            ['name' => 'Administrateur', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        $cashier = User::firstOrCreate(
            ['email' => 'caisse@minimarket.test'],
            ['name' => 'Caissier', 'password' => Hash::make('password'), 'role' => 'vendeur']
        );

        $csvPath = database_path('seeders/data/update_articles_normalized.csv');
        if (!file_exists($csvPath)) {
            throw new \RuntimeException("CSV introuvable: {$csvPath}");
        }

        $toDecimal = function (?string $value): ?float {
            $value = trim((string) $value);
            if ($value === '') {
                return null;
            }
            $value = str_replace(',', '.', $value);
            return (float) $value;
        };

        $toInt = function (?string $value): int {
            $value = trim((string) $value);
            if ($value === '') {
                return 0;
            }
            $value = str_replace(',', '.', $value);
            return (int) round((float) $value);
        };

        $categoryMap = [];
        $productModels = [];
        $seenSkus = [];
        $seenBarcodes = [];

        if (($handle = fopen($csvPath, 'r')) !== false) {
            $header = fgetcsv($handle, 0, ';');
            if ($header === false) {
                throw new \RuntimeException("CSV vide: {$csvPath}");
            }

            $header = array_map('trim', $header);
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $row = array_pad($row, count($header), '');
                $data = array_combine($header, $row);

                $name = trim((string) ($data['NAME'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $categoryRaw = trim((string) ($data['CATEGORY'] ?? ''));
                $categoryName = $categoryRaw !== '' ? Str::title(Str::lower($categoryRaw)) : 'Divers';
                $categoryKey = Str::lower($categoryName);
                if (!isset($categoryMap[$categoryKey])) {
                    $categoryMap[$categoryKey] = Category::firstOrCreate(['name' => $categoryName]);
                }

                $sku = trim((string) ($data['SKU'] ?? ''));
                if ($sku === '' || isset($seenSkus[$sku])) {
                    $sku = 'SKU-' . str_pad((string) (count($seenSkus) + 1), 5, '0', STR_PAD_LEFT);
                }
                $seenSkus[$sku] = true;

                $barcode = trim((string) ($data['BARCODE'] ?? ''));
                if ($barcode === '') {
                    $barcode = null;
                }
                if ($barcode !== null && isset($seenBarcodes[$barcode])) {
                    $barcode = null;
                }
                if ($barcode !== null) {
                    $seenBarcodes[$barcode] = true;
                }

                $unit = trim((string) ($data['UNIT'] ?? ''));
                $cost = $toDecimal($data['COST PRICE'] ?? null);
                $sale = $toDecimal($data['SALE PRICE'] ?? null);
                $currency = strtoupper(trim((string) ($data['CURRENCY'] ?? 'CDF')));
                $stockQty = $toInt($data['STOCK QTITE'] ?? null);
                $minStock = $toInt($data['MIN STOCK'] ?? null);
                $reorderQty = $toInt($data['REORD QTE'] ?? null);

                $product = Product::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'name' => $name,
                        'category_id' => $categoryMap[$categoryKey]->id,
                        'barcode' => $barcode,
                        'unit' => $unit !== '' ? $unit : null,
                        'cost_price' => $cost,
                        'sale_price' => $sale,
                        'promo_label' => null,
                        'promo_price' => null,
                        'image_url' => null,
                        'currency' => $currency !== '' ? $currency : 'CDF',
                        'min_stock' => $minStock,
                        'reorder_qty' => $reorderQty,
                    ]
                );

                $productModels[] = $product;

                Stock::updateOrCreate(
                    ['product_id' => $product->id],
                    ['quantity' => $stockQty]
                );
            }

            fclose($handle);
        }

        // Production: no example data for purchases, sales, invoices, or stock movements.
    }
}
