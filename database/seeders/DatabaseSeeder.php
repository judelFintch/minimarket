<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

        $suppliers = [
            ['name' => 'DistribPlus', 'contact' => 'Amine', 'phone' => '060000001', 'email' => 'contact@distribplus.test', 'address' => 'Zone industrielle'],
            ['name' => 'FreshMarket', 'contact' => 'Lea', 'phone' => '060000002', 'email' => 'hello@freshmarket.test', 'address' => 'Avenue Centrale'],
            ['name' => 'Boissons & Co', 'contact' => 'Karim', 'phone' => '060000003', 'email' => 'sales@boissonsco.test', 'address' => 'Quartier Nord'],
        ];

        $supplierModels = [];
        foreach ($suppliers as $supplierData) {
            $supplierModels[] = Supplier::firstOrCreate(
                ['name' => $supplierData['name']],
                [
                    'contact_name' => $supplierData['contact'],
                    'phone' => $supplierData['phone'],
                    'email' => $supplierData['email'],
                    'address' => $supplierData['address'],
                ]
            );
        }

        for ($i = 0; $i < 5; $i++) {
            $supplier = $supplierModels[array_rand($supplierModels)];
            $purchase = Purchase::create([
                'user_id' => $admin->id,
                'supplier_id' => $supplier->id,
                'reference' => 'PUR-' . now()->subDays(10 - $i)->format('Ymd') . '-' . strtoupper(str()->random(4)),
                'total_amount' => 0,
                'status' => 'received',
                'purchased_at' => now()->subDays(10 - $i),
            ]);

            $totalAmount = 0;
            $itemsCount = $faker->numberBetween(2, 5);
            $pickedProducts = $faker->randomElements($productModels, $itemsCount);

            foreach ($pickedProducts as $product) {
                $qty = $faker->numberBetween(5, 20);
                $unitCost = (float) ($product->cost_price ?? $faker->randomFloat(2, 0.5, 4));
                $lineTotal = $qty * $unitCost;
                $totalAmount += $lineTotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'line_total' => $lineTotal,
                ]);

                $stock = Stock::firstOrCreate(['product_id' => $product->id], ['quantity' => 0]);
                $stock->update(['quantity' => $stock->quantity + $qty]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => 'in',
                    'quantity' => $qty,
                    'reason' => 'Achat ' . $purchase->reference,
                    'occurred_at' => $purchase->purchased_at,
                ]);
            }

            $purchase->update(['total_amount' => $totalAmount]);
        }

        $createSaleTotals = function (array $items, float $discountRate, float $taxRate): array {
            $subtotal = 0;
            foreach ($items as $item) {
                $lineBase = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $lineBase * ($item['discount_rate'] / 100);
                $subtotal += $lineBase - $lineDiscount;
            }

            $discountAmount = $subtotal * ($discountRate / 100);
            $taxable = $subtotal - $discountAmount;
            $taxAmount = $taxable * ($taxRate / 100);

            return [
                'subtotal' => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'tax_amount' => round($taxAmount, 2),
                'total' => round($taxable + $taxAmount, 2),
            ];
        };

        for ($i = 0; $i < 6; $i++) {
            $itemsCount = $faker->numberBetween(2, 4);
            $pickedProducts = $faker->randomElements($productModels, $itemsCount);

            $items = [];
            foreach ($pickedProducts as $product) {
                $qty = $faker->numberBetween(1, 3);
                $price = $product->promo_price !== null ? (float) $product->promo_price : (float) $product->sale_price;
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_rate' => (float) $faker->randomElement([0, 0, 5, 10]),
                ];
            }

            $discountRate = (float) $faker->randomElement([0, 0, 3, 5]);
            $taxRate = (float) $faker->randomElement([0, 10, 18]);
            $totals = $createSaleTotals($items, $discountRate, $taxRate);

            $sale = Sale::create([
                'user_id' => $cashier->id,
                'reference' => 'SALE-' . now()->subDays(5 - $i)->format('YmdHis') . '-' . strtoupper(str()->random(4)),
                'customer_name' => $faker->name(),
                'total_amount' => $totals['total'],
                'subtotal_amount' => $totals['subtotal'],
                'discount_rate' => $discountRate,
                'discount_amount' => $totals['discount_amount'],
                'tax_rate' => $taxRate,
                'tax_amount' => $totals['tax_amount'],
                'status' => 'paid',
                'sold_at' => now()->subDays(5 - $i),
            ]);

            foreach ($items as $item) {
                $lineBase = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $lineBase * ($item['discount_rate'] / 100);
                $lineTotal = $lineBase - $lineDiscount;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_rate' => $item['discount_rate'],
                    'discount_amount' => $lineDiscount,
                    'line_total' => $lineTotal,
                ]);

                $stock = Stock::firstOrCreate(['product_id' => $item['product_id']], ['quantity' => 0]);
                $stock->update(['quantity' => max(0, $stock->quantity - $item['quantity'])]);

                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reason' => 'Vente ' . $sale->reference,
                    'occurred_at' => $sale->sold_at,
                ]);
            }

            Invoice::create([
                'sale_id' => $sale->id,
                'invoice_number' => 'INV-' . now()->subDays(5 - $i)->format('YmdHis') . '-' . strtoupper(str()->random(4)),
                'total_amount' => $totals['total'],
                'status' => 'paid',
                'issued_at' => $sale->sold_at,
                'due_at' => $sale->sold_at,
            ]);
        }

        for ($i = 0; $i < 2; $i++) {
            $itemsCount = $faker->numberBetween(2, 3);
            $pickedProducts = $faker->randomElements($productModels, $itemsCount);

            $items = [];
            foreach ($pickedProducts as $product) {
                $qty = $faker->numberBetween(1, 3);
                $price = $product->promo_price !== null ? (float) $product->promo_price : (float) $product->sale_price;
                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_rate' => 0,
                ];
            }

            $totals = $createSaleTotals($items, 0, 0);

            Sale::create([
                'user_id' => $cashier->id,
                'reference' => 'SALE-' . now()->format('YmdHis') . '-' . strtoupper(str()->random(4)),
                'customer_name' => $faker->name(),
                'total_amount' => $totals['total'],
                'subtotal_amount' => $totals['subtotal'],
                'discount_rate' => 0,
                'discount_amount' => 0,
                'tax_rate' => 0,
                'tax_amount' => 0,
                'status' => 'pending',
                'sold_at' => now(),
            ])->items()->createMany(
                collect($items)->map(function ($item) {
                    $lineBase = $item['quantity'] * $item['unit_price'];
                    return [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount_rate' => $item['discount_rate'],
                        'discount_amount' => 0,
                        'line_total' => $lineBase,
                    ];
                })->all()
            );
        }

        if (Schema::hasTable('favorite_products')) {
            $favoriteIds = collect($productModels)->pluck('id')->take(5)->all();
            $admin->favoriteProducts()->syncWithoutDetaching($favoriteIds);
            $cashier->favoriteProducts()->syncWithoutDetaching($favoriteIds);
        }
    }
}
