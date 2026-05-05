<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Shop;
use App\Models\OrderDetail;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class SalesExport extends BaseExport
{
    protected function buildQuery()
    {
        $type = $this->filters['type'] ?? 'in_house';

        if ($type == 'seller') {
            // For seller-based report
            $query = Shop::with(['user', 'user.products'])
                ->whereHas('user'); // Only sellers with valid users
            return $query;
        } else {
            // For in-house product report
            $query = Product::with(['stocks', 'main_category', 'user', 'shop'])
                ->where('added_by', 'admin')
                ->select(
                    'id',
                    'name',
                    'category_id',
                    'unit_price',
                    'approved',
                    'num_of_sale',
                    'seller_featured',
                    'added_by'
                );

            // Apply category filter if provided
            if (isset($this->filters['category_id']) && $this->filters['category_id']) {
                $query->where('category_id', $this->filters['category_id']);
            }

            return $query;
        }
    }

    public function headings(): array
    {
        $type = $this->filters['type'] ?? 'in_house';

        if ($type == 'seller') {
            // Seller-based report headings
            return [
                translate('Seller Name'),
                translate('Shop Name'),
                translate('Number of Product Sale'),
                translate('Order Amount'),
            ];
        } else {
            // In-house product report headings
            return [
                translate('Product Name'),
                translate('Num of Sale'),
            ];
        }
    }

    public function map($model): array
    {
        $type = $this->filters['type'] ?? 'in_house';

        if ($type == 'seller') {
            \Log::info('Mapping seller data for export', ['seller_id' => $model->id]);
            // Map seller data
            if (!$model || !$model->user) {
                return [];
            }

            $numOfSale = 0;
            if ($model->user->products) {
                foreach ($model->user->products as $product) {
                    $numOfSale += $product->num_of_sale ?? 0;
                }
            }

            $orderAmount = OrderDetail::where('seller_id', $model->user->id)
                ->where('delivery_status', '!=', 'cancelled')
                ->sum('price');

            return [
                $model->user->name ?? '--',
                $model->user->shop ? $model->user->shop->name : '--',
                $numOfSale,
                single_price($orderAmount), // Return raw number for proper formatting
            ];
        } else {
            // Map in-house product data
            return [
                $model->getTranslation('name'),
                $model->num_of_sale ?? 0,
            ];
        }
    }

    protected function calculateTotals(): ?array
    {
        return null;
    }
}
