<?php

namespace App\Exports;

use App\Models\User;

class UsersExport extends BaseExport
{
    protected function buildQuery()
    {
        return User::with(['orders', 'refund_requests', 'addresses'])
            ->select('id', 'name', 'email', 'phone', 'balance', 'created_at')
            ->where('user_type', 'customer');
    }

    public function headings(): array
    {
        return [
            translate('Name'),
            translate('Email'),
            translate('Phone'),
            translate('Address'),
            translate('State'),
            translate('City'),
            translate('Orders Count'),
            translate('Paid Amount'),
            translate('Unpaid Amount'),
            translate('Refund Amount'),
            translate('Balance'),
        ];
    }

    public function map($user): array
    {
        $address = $user->addresses->first();

        return [
            $user->name,
            $user->email,
            $user->phone,
            $address ? $address->address : '',
            $address ? optional($address->state)->name : '',
            $address ? optional($address->city)->name : '',
            $user->orders_count,
            single_price($user->paid_amount),
            single_price($user->unpaid_amount),
            single_price($user->refund_amount),
            single_price($user->balance),
        ];
    }

    protected function calculateTotals(): ?array
    {
        $baseQuery = User::query()->where('user_type', 'customer');

        if ($this->ids) {
            $baseQuery->whereIn('id', $this->ids);
        }

        $totals = [
            'orders_count' => 0,
            'paid_amount' => 0,
            'unpaid_amount' => 0,
            'refund_amount' => 0,
            'balance' => 0,
        ];

        $baseQuery->chunk(1000, function ($users) use (&$totals) {
            foreach ($users as $user) {
                $user->loadMissing(['orders', 'refund_requests']);

                $totals['orders_count'] += $user->orders->count();
                $totals['paid_amount'] += $user->paid_amount;
                $totals['unpaid_amount'] += $user->unpaid_amount;
                $totals['refund_amount'] += $user->refund_amount;
                $totals['balance'] += $user->balance;
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
            '',
            '',
            $totals['orders_count'],
            single_price($totals['paid_amount']),
            single_price($totals['unpaid_amount']),
            single_price($totals['refund_amount']),
            single_price($totals['balance']),
        ];
    }
}
