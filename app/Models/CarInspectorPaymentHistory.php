<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarInspectorPaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_inspector_id',
        'type',
        'amount',
        'description',
        'payment_method',
        'payment_details',
        'status',
        'processed_by',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'json',
    ];

    const TYPE_EARNING = 'earning';
    const TYPE_PAYMENT = 'payment';
    const TYPE_ADJUSTMENT = 'adjustment';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the car inspector that owns the payment history.
     */
    public function carInspector()
    {
        return $this->belongsTo(CarInspector::class);
    }

    /**
     * Get the user who processed this payment.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope a query to only include earnings.
     */
    public function scopeEarnings($query)
    {
        return $query->where('type', self::TYPE_EARNING);
    }

    /**
     * Scope a query to only include payments.
     */
    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    /**
     * Scope a query to only include adjustments.
     */
    public function scopeAdjustments($query)
    {
        return $query->where('type', self::TYPE_ADJUSTMENT);
    }

    /**
     * Scope a query to only include completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to only include pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            self::TYPE_EARNING => 'Earning',
            self::TYPE_PAYMENT => 'Payment',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get the status display name.
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => translate('Pending'),
            self::STATUS_COMPLETED => translate('Completed'),
            self::STATUS_FAILED => translate('Failed'),
            self::STATUS_CANCELLED => translate('Cancelled'),
            default => ucfirst($this->status),
        };
    }

    /**
     * Get the formatted amount with currency.
     */
    public function getFormattedAmountAttribute()
    {
        $currency = get_setting('system_default_currency') ?? 'USD';
        return format_price($this->amount);
    }

    /**
     * Get the status badge class for display.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_FAILED => 'badge-danger',
            self::STATUS_CANCELLED => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    /**
     * Get the type badge class for display.
     */
    public function getTypeBadgeClassAttribute()
    {
        return match($this->type) {
            self::TYPE_EARNING => 'badge-success',
            self::TYPE_PAYMENT => 'badge-primary',
            self::TYPE_ADJUSTMENT => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /**
     * Check if the transaction is earnings.
     */
    public function isEarning()
    {
        return $this->type === self::TYPE_EARNING;
    }

    /**
     * Check if the transaction is a payment.
     */
    public function isPayment()
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    /**
     * Check if the transaction is an adjustment.
     */
    public function isAdjustment()
    {
        return $this->type === self::TYPE_ADJUSTMENT;
    }

    /**
     * Check if the transaction is completed.
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the transaction is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the transaction is failed.
     */
    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark the transaction as completed.
     */
    public function markAsCompleted()
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark the transaction as failed.
     */
    public function markAsFailed($reason = null)
    {
        $data = ['status' => self::STATUS_FAILED];
        if ($reason) {
            $data['notes'] = $reason;
        }
        $this->update($data);
    }

    /**
     * Mark the transaction as cancelled.
     */
    public function markAsCancelled($reason = null)
    {
        $data = ['status' => self::STATUS_CANCELLED];
        if ($reason) {
            $data['notes'] = $reason;
        }
        $this->update($data);
    }
}
