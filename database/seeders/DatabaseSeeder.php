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

        $categories = [
            'Epicerie',
            'Boissons',
            'Produits frais',
            'Hygiene',
            'Snacking',
            'Boulangerie',
        ];

        $categoryMap = [];
        foreach ($categories as $categoryName) {
            $categoryMap[$categoryName] = Category::firstOrCreate(['name' => $categoryName]);
        }

        $products = [
            ['name' => 'Riz 1kg', 'category' => 'Epicerie', 'sku' => 'RIZ-001', 'barcode' => '100000000001', 'unit' => 'kg', 'cost' => 2.20, 'sale' => 3.90],
            ['name' => 'Pates 500g', 'category' => 'Epicerie', 'sku' => 'PAT-001', 'barcode' => '100000000002', 'unit' => 'g', 'cost' => 0.80, 'sale' => 1.60],
            ['name' => 'Huile 1L', 'category' => 'Epicerie', 'sku' => 'HUI-001', 'barcode' => '100000000003', 'unit' => 'L', 'cost' => 2.90, 'sale' => 4.50],
            ['name' => 'Jus d\'orange 1L', 'category' => 'Boissons', 'sku' => 'JUS-001', 'barcode' => '100000000004', 'unit' => 'L', 'cost' => 1.50, 'sale' => 2.50],
            ['name' => 'Eau minerale 1.5L', 'category' => 'Boissons', 'sku' => 'EAU-001', 'barcode' => '100000000005', 'unit' => 'L', 'cost' => 0.60, 'sale' => 1.20],
            ['name' => 'Lait 1L', 'category' => 'Produits frais', 'sku' => 'LAIT-001', 'barcode' => '100000000006', 'unit' => 'L', 'cost' => 0.90, 'sale' => 1.60],
            ['name' => 'Yaourt nature', 'category' => 'Produits frais', 'sku' => 'YAO-001', 'barcode' => '100000000007', 'unit' => 'piece', 'cost' => 0.25, 'sale' => 0.60],
            ['name' => 'Savon 200g', 'category' => 'Hygiene', 'sku' => 'SAV-001', 'barcode' => '100000000008', 'unit' => 'g', 'cost' => 0.70, 'sale' => 1.40],
            ['name' => 'Dentifrice 75ml', 'category' => 'Hygiene', 'sku' => 'DEN-001', 'barcode' => '100000000009', 'unit' => 'ml', 'cost' => 1.20, 'sale' => 2.30],
            ['name' => 'Chips nature 140g', 'category' => 'Snacking', 'sku' => 'CHI-001', 'barcode' => '100000000010', 'unit' => 'g', 'cost' => 0.90, 'sale' => 1.90],
            ['name' => 'Biscuit chocolat', 'category' => 'Snacking', 'sku' => 'BIS-001', 'barcode' => '100000000011', 'unit' => 'piece', 'cost' => 0.50, 'sale' => 1.20],
            ['name' => 'Baguette', 'category' => 'Boulangerie', 'sku' => 'BAG-001', 'barcode' => '100000000012', 'unit' => 'piece', 'cost' => 0.40, 'sale' => 0.90],
        ];

        $productModels = [];
        foreach ($products as $index => $productData) {
            $product = Product::firstOrCreate(
                ['sku' => $productData['sku']],
                [
                    'name' => $productData['name'],
                    'category_id' => $categoryMap[$productData['category']]->id,
                    'barcode' => $productData['barcode'],
                    'unit' => $productData['unit'],
                    'cost_price' => $productData['cost'],
                    'sale_price' => $productData['sale'],
                    'promo_label' => $index % 4 === 0 ? 'Promo' : null,
                    'promo_price' => $index % 4 === 0 ? round($productData['sale'] * 0.85, 2) : null,
                    'image_url' => 'https://picsum.photos/seed/produit' . ($index + 1) . '/400/300',
                ]
            );

            $productModels[] = $product;

            Stock::updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $faker->numberBetween(20, 120)]
            );
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
