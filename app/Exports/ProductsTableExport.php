<?php

namespace App\Exports;

use App\Models\Product;

class ProductsTableExport extends BaseExport
{
    protected function buildQuery()
    {
        $query = Product::with(['stocks', 'main_category'])
            ->select(
                'id',
                'name',
                'category_id',
                'unit_price',
                'approved',
                'published',
                'seller_featured'
            );

        // Filter by seller if user_id is provided in filters
        if (isset($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        return $query;
    }

    public function headings(): array
    {
        $headings = [
            'Name',
            'Current Qty',
            'Base Price',
        ];

        // Add Approval column if product approval is enabled
        if (get_setting('product_approve_by_admin') == 1) {
            $headings[] = 'Approval';
        }

        $headings[] = 'Published';
        $headings[] = 'Featured';

        return $headings;
    }

    public function map($product): array
    {
        // Calculate total quantity from stocks
        $qty = 0;
        foreach ($product->stocks as $stock) {
            $qty += $stock->qty;
        }

        $row = [
            $product->getTranslation('name'),
            $qty,
            $product->unit_price,
        ];

        // Add approval status if enabled
        if (get_setting('product_approve_by_admin') == 1) {
            $row[] = $product->approved == 1 ? 'Approved' : 'Pending';
        }

        $row[] = $product->published == 1 ? 'Yes' : 'No';
        $row[] = $product->seller_featured == 1 ? 'Yes' : 'No';

        return $row;
    }

    protected function calculateTotals(): ?array
    {
        return null;
    }
}
