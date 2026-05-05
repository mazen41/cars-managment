<?php

namespace App\Exports;

use App\Models\Payout;

class PayoutExport extends BaseExport
{
    protected function buildQuery()
    {
        return Payout::with(['user', 'shop'])
            ->select('id', 'amount', 'seller_id','payment_method', 'txn_code', 'payment_details');
    }

    public function headings(): array
    {
        return [
            translate('Seller'),
            translate('Amount'),
            translate('Payment Method'),
            translate('Txn Code'),
            translate('Payment Details'),
        ];
    }

    public function map($payment): array
    {
        $seller_name = $payment->shop->user->name;
        if ($payment->shop) {
            $seller_name .= " ({$payment->shop->name})";
        }

        return [
            $seller_name,
            single_price($payment->amount),
            translate(ucfirst(str_replace('_', ' ', $payment->payment_method))),
            $payment->txn_code,
            $payment->payment_details,
        ];
    }

    protected function calculateTotals(): ?array
    {
        $baseQuery = Payout::query();

        if ($this->ids) {
            $baseQuery->whereIn('id', $this->ids);
        }

        $totals = [
            'amount' => 0,
        ];

        $baseQuery->chunk(1000, function ($payouts) use (&$totals) {
            foreach ($payouts as $payout) {
                $totals['amount'] += $payout->amount;
            }
        });

        return $totals;
    }

    protected function formatTotalsRow(array $totals): array
    {
        return [
            translate('Total'),
            single_price($totals['amount']),
            '',
            '',
            '',
        ];
    }
}
