<?php

namespace App\Services;

use App\Models\CustomerProduct;

class CustomerProductViewTrackingService extends ViewTrackingService
{
    protected function getModelClass(): string
    {
        return CustomerProduct::class;
    }

    protected function getTable(): string
    {
        return 'customer_products';
    }

    protected function getNamespace(): string
    {
        return 'customer_product';
    }
}
