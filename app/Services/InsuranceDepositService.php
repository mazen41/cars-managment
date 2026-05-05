<?php

namespace App\Services;

use App\Enums\PaymentStatusEnum;
use App\Models\User;
use App\Models\UserInsuranceDeposit;
use App\Models\Payment;
use App\Models\AuctionAuditLog;
use Illuminate\Support\Facades\DB;

class InsuranceDepositService
{
    /**
     * Create a new insurance deposit for a user
     *
     * @param User $user
     * @param float $amount
     * @return UserInsuranceDeposit
     */
    public function createDeposit(User $user, float $amount): UserInsuranceDeposit
    {
        return DB::transaction(function () use ($user, $amount) {
            // Check if user already has an active deposit
            $existingDeposit = UserInsuranceDeposit::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'paid'])
                ->first();

            if ($existingDeposit) {
                return $existingDeposit;
            }

            $deposit = UserInsuranceDeposit::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'status' => 'pending',
            ]);

            // Log the deposit creation
            AuctionAuditLog::create([
                'user_id' => $user->id,
                'action' => 'insurance_deposit_created',
                'details' => [
                    'deposit_id' => $deposit->id,
                    'amount' => $amount,
                ],
                'ip_address' => request()->ip(),
            ]);

            return $deposit;
        });
    }

    /**
     * Process payment for an insurance deposit
     *
     * @param UserInsuranceDeposit $deposit
     * @param Payment $payment
     * @return bool
     */
    public function processPayment(UserInsuranceDeposit $deposit, Payment $payment): bool
    {
        return DB::transaction(function () use ($deposit, $payment) {
            $deposit->update([
                'status' => PaymentStatusEnum::PAID,
                'payment_id' => $payment->id,
                'paid_at' => now(),
            ]);

            $payment->update([
                'status'=> PaymentStatusEnum::PAID
            ]);

            // Log the payment
            AuctionAuditLog::create([
                'user_id' => $deposit->user_id,
                'action' => 'insurance_deposit_paid',
                'details' => [
                    'deposit_id' => $deposit->id,
                    'payment_id' => $payment->id,
                    'amount' => $deposit->amount,
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Cancel payment for an insurance deposit
     *
     * @param UserInsuranceDeposit $deposit
     * @param Payment $payment
     * @return bool
     */
    public function cancelPayment(UserInsuranceDeposit $deposit, Payment $payment): bool
    {
        return DB::transaction(function () use ($deposit, $payment) {
            $deposit->update([
                'status' => PaymentStatusEnum::CANCELLED,
                'payment_id' => $payment->id,
            ]);

            $payment->update([
                'status'=> PaymentStatusEnum::CANCELLED
            ]);

            // Log the payment
            AuctionAuditLog::create([
                'user_id' => $deposit->user_id,
                'action' => 'insurance_deposit_cancelled',
                'details' => [
                    'deposit_id' => $deposit->id,
                    'payment_id' => $payment->id,
                    'amount' => $deposit->amount,
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Request a refund for a user's insurance deposit
     *
     * @param User $user
     * @param UserInsuranceDeposit $deposit
     * @param string|null $reason
     * @return bool
     */
    public function requestRefund(User $user, UserInsuranceDeposit $deposit, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($user, $deposit, $reason) {
            if (!$this->canRefund($user)) {
                return false;
            }

            // Mark the refund as requested
            $deposit->update([
                'refund_requested' => true,
                'refund_requested_at' => now(),
            ]);

            // Log the refund request
            AuctionAuditLog::create([
                'user_id' => $user->id,
                'action' => 'insurance_deposit_refund_requested',
                'details' => [
                    'deposit_id' => $deposit->id,
                    'amount' => $deposit->amount,
                    'reason' => $reason,
                ],
                'ip_address' => request()->ip(),
            ]);
            // Dispatch event for notifications
            event(new \App\Events\AuctionInsuranceDepositRefundRequested($deposit, $reason));

            return true;
        });
    }

    /**
     * Check if a user can refund their insurance deposit
     *
     * @param User $user
     * @return bool
     */
    public function canRefund(User $user): bool
    {
        // Check if user has a paid deposit

        if (!$user->hasInsuranceDeposit()) {
            return false;
        }


        if ($user->hasUnpaidAuctionInvoices()) {
            return false;
        }

        return true;
    }

    /**
     * Process a refund for an insurance deposit
     *
     * @param UserInsuranceDeposit $deposit
     * @return Payment|null
     */
    public function processRefund(UserInsuranceDeposit $deposit, $transaction_id): ?Payment
    {
        return DB::transaction(function () use ($deposit, $transaction_id) {
            // Get the original payment to determine refund method
            $originalPayment = $deposit->payment;

            if (!$originalPayment) {
                \Log::error('Cannot process refund: Original payment not found', [
                    'deposit_id' => $deposit->id
                ]);
                return null;
            }
            // Note: We use a placeholder payable type to avoid unique constraint with original payment
            // The refund is tracked via refund_payment_id in the deposit record
            $refundPayment = Payment::forceCreate([
                'payable_type' => 'App\Models\UserInsuranceDepositRefund', // Placeholder type
                'payable_id' => $deposit->id,
                'method' => $originalPayment->method,
                'status' => 'refunded',
                'amount' => $deposit->amount,
                'transaction_id' =>  $transaction_id,
                'reference_id' => 'REFUND-' . $originalPayment->reference_id,
                'details' => [
                    'type' => 'insurance_deposit_refund',
                    'deposit_id' => $deposit->id,
                    'original_payment_id' => $originalPayment->id,
                    'original_transaction_id' => $originalPayment->transaction_id,
                ],
                'paid_at' => now(),
            ]);

            // Update deposit status
            $deposit->update([
                'status' => 'refunded',
                'refund_payment_id' => $refundPayment->id,
                'refunded_at' => now(),
            ]);

            // Log the refund
            AuctionAuditLog::create([
                'user_id' => $deposit->user_id,
                'action' => 'insurance_deposit_refunded',
                'details' => [
                    'deposit_id' => $deposit->id,
                    'refund_payment_id' => $refundPayment->id,
                    'amount' => $deposit->amount,
                    'original_payment_id' => $originalPayment->id,
                ],
                'ip_address' => request()->ip(),
            ]);

            // For now, we're creating the refund record in the database

            return $refundPayment;
        });
    }

    /**
     * Check eligibility for bidding (deposit)
     *
     * @param User $user
     * @return array
     */
    public function checkEligibility(User $user): array
    {
        $eligible = true;
        $reasons = [];


        // Check insurance deposit
        if (!$user->hasInsuranceDeposit()) {
            $eligible = false;
            $reasons[] = 'Insurance deposit payment required';
        }

        return [
            'eligible' => $eligible,
            'reasons' => $reasons,
            'has_deposit' => $user->hasInsuranceDeposit(),
        ];
    }

    /**
     * Get filtered and paginated deposits for admin panel
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getFilteredDeposits(array $filters = [], int $perPage = 20)
    {
        $query = UserInsuranceDeposit::query()->with(['user', 'payment', 'refundPayment']);

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        // Apply sorting
        if (isset($filters['sort_by'])) {
            $sortDirection = $filters['sort_direction'] ?? 'asc';

            switch ($filters['sort_by']) {
                case 'amount':
                    $query->orderBy('amount', $sortDirection);
                    break;
                case 'date':
                case 'paid_at':
                    $query->orderBy('paid_at', $sortDirection);
                    break;
                case 'customer_name':
                    $query->join('users', 'user_insurance_deposits.user_id', '=', 'users.id')
                         ->orderBy('users.name', $sortDirection)
                         ->select('user_insurance_deposits.*');
                    break;
                case 'status':
                    $query->orderBy('status', $sortDirection);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            // Default sorting by creation date
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage)->appends($filters);
    }

    /**
     * Get deposit statistics
     *
     * @param array $filters
     * @return array
     */
    public function getDepositStatistics(array $filters = []): array
    {
        $query = UserInsuranceDeposit::query();

        // Apply filters to statistics query
        $query = $this->applyFilters($query, $filters);

        // Calculate statistics
        $totalCount = (clone $query)->count();
        $paidAmount = (clone $query)->where('status', 'paid')->sum('amount');
        $refundedAmount = (clone $query)->where('status', 'refunded')->sum('amount');
        $pendingCount = (clone $query)->where('status', 'pending')->count();

        return [
            'total_count' => $totalCount,
            'paid_amount' => $paidAmount,
            'refunded_amount' => $refundedAmount,
            'pending_count' => $pendingCount,
        ];
    }

    /**
     * Apply filters to the query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyFilters($query, array $filters)
    {
        // Filter by status
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search by customer name or email
        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter by date range
        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->whereDate('paid_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->whereDate('paid_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Update insurance deposit for pending insucances
     * @param UserInsuranceDeposit
     * @param string $status
     * @return bool
     */
    public function updatePaymentStatus(UserInsuranceDeposit $deposit, string $status): bool
    {
        $original_payment = $deposit->payment;
        if(!$original_payment){
            return false;
        }
        if($original_payment->status != PaymentStatusEnum::PENDING || $deposit->status != PaymentStatusEnum::PENDING){
        return false;
        }

        if($status == PaymentStatusEnum::PAID){
            return $this->processPayment($deposit, $original_payment);
        }

        if($status == PaymentStatusEnum::CANCELLED){
            return $this->cancelPayment($deposit, $original_payment);
        }
        return false;
    }
}
