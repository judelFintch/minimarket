<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $categories = [
            'Epicerie',
            'Boissons',
            'Produits frais',
        ];

        $categoryMap = [];
        foreach ($categories as $categoryName) {
            $categoryMap[$categoryName] = Category::firstOrCreate([
                'name' => $categoryName,
            ]);
        }

        $products = [
            [
                'name' => 'Riz 1kg',
                'category' => 'Epicerie',
                'sku' => 'RIZ-001',
                'sale_price' => 3.90,
                'stock' => 50,
            ],
            [
                'name' => 'Jus d\'orange 1L',
                'category' => 'Boissons',
                'sku' => 'JUS-001',
                'sale_price' => 2.50,
                'stock' => 30,
            ],
            [
                'name' => 'Lait 1L',
                'category' => 'Produits frais',
                'sku' => 'LAIT-001',
                'sale_price' => 1.60,
                'stock' => 40,
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::firstOrCreate(
                ['sku' => $productData['sku']],
                [
                    'name' => $productData['name'],
                    'category_id' => $categoryMap[$productData['category']]->id,
                    'sale_price' => $productData['sale_price'],
                ]
            );

            Stock::updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $productData['stock']]
            );
        }
    }
}
