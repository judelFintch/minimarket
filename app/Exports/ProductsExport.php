<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function query(): Builder
    {
        return Product::query()
            ->active()
            ->with(['category', 'stock'])
            ->orderBy('name');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->sku,
            $row->barcode,
            $row->unit,
            $row->cost_price,
            $row->sale_price,
            $row->currency ?? 'CDF',
            $row->stock?->quantity ?? 0,
            $row->min_stock ?? 0,
            $row->reorder_qty ?? 0,
            $row->category?->name,
            $row->category_id,
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
