<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInsuranceDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'payment_id',
        'refund_payment_id',
        'refund_requested',
        'refund_requested_at',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the user who owns this deposit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment record for this deposit.
     */
    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    /**
     * Get the refund payment record for this deposit.
     */
    public function refundPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'refund_payment_id');
    }

    /**
     * Scope a query to only include paid deposits.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include deposits for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to filter by status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, ?string $status)
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope a query to search by customer name or email.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $term
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->whereHas('user', function ($q) use ($term) {
            $q->where('name', 'like', '%' . $term . '%')
              ->orWhere('email', 'like', '%' . $term . '%');
        });
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, ?string $startDate, ?string $endDate)
    {
        if (!empty($startDate)) {
            $query->where('created_at', '>=', $startDate);
        }

        if (!empty($endDate)) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope a query to eager load relationships.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithRelations($query)
    {
        return $query->with(['user', 'payment', 'refundPayment']);
    }

    /**
     * Check if the deposit has been paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the deposit can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'paid' && !$this->refunded_at;
    }

    /**
     * Mark the deposit as paid.
     */
    public function markAsPaid(Payment $payment): void
    {
        $this->status = 'paid';
        $this->payment_id = $payment->id;
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Mark the deposit as refunded.
     */
    public function markAsRefunded(Payment $refundPayment): void
    {
        $this->status = 'refunded';
        $this->refund_payment_id = $refundPayment->id;
        $this->refunded_at = now();
        $this->save();
    }
}
