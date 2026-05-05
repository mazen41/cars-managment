<?php

namespace App\Exports;

use App\Models\DeliveryBoyPayment;

class DeliveryBoyPaymentHistoriesExport extends BaseExport
{
    protected function buildQuery()
    {
        return DeliveryBoyPayment::with('user')
            ->select('id', 'user_id', 'payment', 'created_at');
    }

    public function headings(): array
    {
        return [
            translate('Delivery Boy'),
            translate('Payment'),
            translate('Date'),
        ];
    }

    public function map($payment_history): array
    {
        return [
            $payment_history->user->name ?? translate('deleted'),
            single_price($payment_history->payment),
            date('d-m-Y', strtotime($payment_history->created_at)),
        ];
    }

    protected function calculateTotals(): ?array
    {
        $baseQuery = DeliveryBoyPayment::query();

        if ($this->ids) {
            $baseQuery->whereIn('id', $this->ids);
        }

        $totals = [
            'payment' => 0,
        ];

        $baseQuery->chunk(1000, function ($payments) use (&$totals) {
            foreach ($payments as $payment) {
                $totals['payment'] += $payment->payment;
            }
        });

        return $totals;
    }

    protected function formatTotalsRow(array $totals): array
    {
        return [
            translate('Total'),
            single_price($totals['payment']),
            '',
        ];
    }
}
