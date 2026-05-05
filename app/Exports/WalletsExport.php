<?php

namespace App\Exports;

use App\Models\Wallet;

class WalletsExport extends BaseExport
{
    protected function buildQuery()
    {
        return Wallet::with('user')
            ->select('id', 'user_id', 'amount', 'offline_payment', 'payment_method', 'approval', 'created_at');
    }

    public function headings(): array
    {
        return [
            translate('ID'),
            translate('User'),
            translate('Amount'),
            translate('Payment Method'),
            translate('Offline Payment'),
            translate('Approval'),
            translate('Date'),
        ];
    }

    public function map($wallet): array
    {
        return [
            $wallet->id,
            $wallet->user ? $wallet->user->name : translate('N/A'),
            single_price($wallet->amount),
            translate(ucfirst(str_replace('_', ' ', $wallet->payment_method))),
            $wallet->offline_payment ? translate('Yes') : translate('No'),
            $wallet->approval ? translate('Approved') : translate('Pending'),
            date('d-m-Y', strtotime($wallet->created_at)),
        ];
    }

    protected function calculateTotals(): ?array
    {
        $baseQuery = Wallet::query();

        if ($this->ids) {
            $baseQuery->whereIn('id', $this->ids);
        }

        $totals = [
            'total_amount' => 0,
            'approved_amount' => 0,
            'pending_amount' => 0,
        ];

        $baseQuery->chunk(1000, function ($wallets) use (&$totals) {
            foreach ($wallets as $wallet) {
                $totals['total_amount'] += $wallet->amount;

                if (!$wallet->offline_payment || ($wallet->offline_payment && $wallet->approval)) {
                    $totals['approved_amount'] += $wallet->amount;
                } else {
                    $totals['pending_amount'] += $wallet->amount;
                }
            }
        });

        return $totals;
    }

    protected function formatTotalsRow(array $totals): array
    {
        return [
            translate('Total'),
            '',
            single_price($totals['total_amount']),
            '',
            '',
            '',
            '',
        ];
    }
}
