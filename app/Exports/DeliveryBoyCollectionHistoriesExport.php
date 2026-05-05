<?php

namespace App\Exports;

use App\Models\DeliveryBoyCollection;

class DeliveryBoyCollectionHistoriesExport extends BaseExport
{
    protected function buildQuery()
    {
        return DeliveryBoyCollection::with('user')
            ->select('id', 'user_id', 'collection_amount', 'created_at');
    }

    public function headings(): array
    {
        return [
            translate('Delivery Boy'),
            translate('Collection Amount'),
            translate('Date'),
        ];
    }

    public function map($collection_history): array
    {
        return [
            $collection_history->user->name ?? translate('deleted'),
            single_price($collection_history->collection_amount),
            date('d-m-Y', strtotime($collection_history->created_at)),
        ];
    }

    protected function calculateTotals(): ?array
    {
        $baseQuery = DeliveryBoyCollection::query();

        if ($this->ids) {
            $baseQuery->whereIn('id', $this->ids);
        }

        $totals = [
            'collection_amount' => 0,
        ];

        $baseQuery->chunk(1000, function ($collections) use (&$totals) {
            foreach ($collections as $collection) {
                $totals['collection_amount'] += $collection->collection_amount;
            }
        });

        return $totals;
    }

    protected function formatTotalsRow(array $totals): array
    {
        return [
            translate('Total'),
            single_price($totals['collection_amount']),
            '',
        ];
    }
}
