<?php

namespace App\Services;

use App\Models\Car;

class CarViewTrackingService extends ViewTrackingService
{
    protected function getModelClass(): string
    {
        return Car::class;
    }

    protected function getTable(): string
    {
        return 'cars';
    }

    protected function getNamespace(): string
    {
        return 'car';
    }
}
