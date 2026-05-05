<?php

namespace App\Exports;

use App\Models\Shop;

class ShopsExport extends BaseExport
{
    protected function buildQuery()
    {
        return Shop::with(['user', 'products', 'payments', 'commissions'])
            ->select('id', 'name', 'user_id', 'address', 'admin_to_pay');
    }

    public function headings(): array
    {
        return [
            translate('Name'),
            translate('Username'),
            translate('Phone'),
            translate('Address'),
            translate('Products Count'),
            translate('Due to seller'),
            translate('Admin Commission'),
            translate('Total Paid Orders'),
            translate('Total payment amount'),
        ];
    }

    public function map($shop): array
    {
        return [
            $shop->name,
            $shop->user->name,
            $shop->user->phone,
            $shop->address ?? '',
            $shop->products->count(),
            single_price($shop->admin_to_pay),
            single_price($shop->total_admin_commission),
            single_price($shop->paid_orders),
            single_price($shop->payments->sum('amount')),
        ];
    }

    protected function calculateTotals(): ?array
    {
        $baseQuery = Shop::with(['products', 'payments', 'commissions']);

        if ($this->ids) {
            $baseQuery->whereIn('id', $this->ids);
        }

        $totals = [
            'products_count' => 0,
            'admin_to_pay' => 0,
            'admin_commission' => 0,
            'paid_orders' => 0,
            'payment_amount' => 0,
        ];

        $baseQuery->chunk(1000, function ($shops) use (&$totals) {
            foreach ($shops as $shop) {
                $totals['products_count'] += $shop->products->count();
                $totals['admin_to_pay'] += $shop->admin_to_pay;
                $totals['admin_commission'] += $shop->total_admin_commission;
                $totals['paid_orders'] += $shop->paid_orders;
                $totals['payment_amount'] += $shop->payments->sum('amount');
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
            '',
            $totals['products_count'],
            single_price($totals['admin_to_pay']),
            single_price($totals['admin_commission']),
            single_price($totals['paid_orders']),
            single_price($totals['payment_amount']),
        ];
    }
}
