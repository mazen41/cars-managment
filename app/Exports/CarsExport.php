<?php

namespace App\Exports;

use App\Models\Car;
use App\Exports\BaseExport;

class CarsExport extends BaseExport
{
    protected function buildQuery()
    {
        return Car::with(['brand', 'model', 'user'])
            ->select('id', 'vin','brand_id', 'model_id', 'manufacture_year', 'user_id', 'car_status', 'moderation_status');
    }

    public function headings(): array
    {
        return [
            translate('vin'),
            translate('Brand'),
            translate('Model'),
            translate('Year'),
            translate('Owner'),
            translate('Moderation Status'),
            translate('Car Status')
        ];
    }

    public function map($car): array
    {
        $owner_name = $car->user->name;
        if ($car->user && $car->user->shop) {
            $owner_name .= " ({$car->user->shop->name})";
        }

        return [
            $car->vin,
            $car->brand->name,
            $car->model->name,
            $car->manufacture_year,
            $owner_name,
            translate(ucfirst($car->moderation_status)),
            translate(ucfirst($car->car_status)),
        ];
    }
}
