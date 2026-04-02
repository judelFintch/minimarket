<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsTemplateExport implements FromArray, ShouldAutoSize, WithStyles
{
    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return [
            [
                'name',
                'sku',
                'barcode',
                'unit',
                'cost_price',
                'sale_price',
                'currency',
                'stock_quantity',
                'min_stock',
                'reorder_qty',
                'category',
                'category_id',
            ],
            [
                'Exemple produit',
                'SKU-001',
                'BAR-001',
                'piece',
                1000,
                1500,
                'CDF',
                10,
                2,
                5,
                'Categorie',
                '',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
