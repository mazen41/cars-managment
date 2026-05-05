<?php

namespace App\Exports;

use App\Models\Product;

class ProductsExport extends BaseExport
{
    protected function buildQuery()
    {
        return Product::with('stocks')
            ->select(
                'id',
                'name',
                'description',
                'added_by',
                'user_id',
                'category_id',
                'brand_id',
                'video_provider',
                'video_link',
                'unit_price',
                'unit',
                'est_shipping_days',
                'meta_title',
                'meta_description'
            );
    }

    public function headings(): array
    {
        return [
            'name',
            'description',
            'added_by',
            'user_id',
            'category_id',
            'brand_id',
            'video_provider',
            'video_link',
            'unit_price',
            'unit',
            'current_stock',
            'est_shipping_days',
            'meta_title',
            'meta_description',
        ];
    }

    public function map($product): array
    {
        $qty = 0;
        foreach ($product->stocks as $stock) {
            $qty += $stock->qty;
        }

        return [
            $product->name,
            $product->description,
            $product->added_by,
            $product->user_id,
            $product->category_id,
            $product->brand_id,
            $product->video_provider,
            $product->video_link,
            $product->unit_price,
            $product->unit,
            $qty,
            $product->est_shipping_days,
            $product->meta_title,
            $product->meta_description,
        ];
    }

    // No totals needed for products export
    protected function calculateTotals(): ?array
    {
        return null;
    }
}
