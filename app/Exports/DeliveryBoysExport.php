<?php

namespace App\Exports;

use App\Models\DeliveryBoy;

class DeliveryBoysExport extends BaseExport
{
    protected function buildQuery()
    {
        return DeliveryBoy::with('user')
            ->select('id', 'user_id', 'total_earning', 'total_collection');
    }

    public function headings(): array
    {
        return [
            translate('Name'),
            translate('Email'),
            translate('Phone'),
            translate('Total Earnings'),
            translate('Total Collection'),
        ];
    }

    public function map($delivery_boy): array
    {
        return [
            $delivery_boy->user->name,
            $delivery_boy->user->email,
            $delivery_boy->user->phone,
            single_price($delivery_boy->total_earning),
            single_price($delivery_boy->total_collection),
        ];
    }

    protected function calculateTotals(): ?array
    {
        $baseQuery = DeliveryBoy::query();

        if ($this->ids) {
            $baseQuery->whereIn('id', $this->ids);
        }

        $totals = [
            'total_earning' => 0,
            'total_collection' => 0,
        ];

        $baseQuery->chunk(1000, function ($deliveryBoys) use (&$totals) {
            foreach ($deliveryBoys as $deliveryBoy) {
                $totals['total_earning'] += $deliveryBoy->total_earning;
                $totals['total_collection'] += $deliveryBoy->total_collection;
            }
        });

        return $totals;
    }

    protected function formatTotalsRow(array $totals): array
    {
        return [
            translate('Total'),
            '',
            '',
            single_price($totals['total_earning']),
            single_price($totals['total_collection']),
        ];
    }
}
