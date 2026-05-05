<?php

namespace App\Exports;

use App\Models\CustomerProduct;
use App\Exports\BaseExport;

class CustomerProductsExport extends BaseExport
{
    protected function buildQuery()
    {
        return CustomerProduct::with(['user'])
            ->select('id', 'name','user_id', 'address','moderation_status', 'price');
    }

    public function headings(): array
    {
        return [
            translate('Customer'),
            translate('Product'),
            translate('address'),
            translate('Price'),
            translate('Moderation Status'),
        ];
    }

    public function map($customerProduct): array
    {
        $customer_name = $customerProduct->user->name;
        if ($customerProduct->user) {
            $customer_name .= " ({$customerProduct->user->phone})";
        }

        return [
            $customer_name,
            $customerProduct->name,
            $customerProduct->address,
            $customerProduct->price,
            translate(ucfirst($customerProduct->moderation_status)),
        ];
    }
}
