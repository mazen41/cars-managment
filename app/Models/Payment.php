<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'method',
        'status',
        'details',
        'is_manual_payment',
        'transaction_id',
        'reference_id',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'is_manual_payment' => 'boolean',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'details' => 'array',
    ];

    /**
     * Get the car inspection that owns the payment.
     */
    public function payable(): BelongsTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include payments with a specific status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include payments with a specific method.
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include unpaid payments.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

     /**
     * Scope a query to only include refunded payments.
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope a query to only include manual payments.
     */
    public function scopeManual($query)
    {
        return $query->where('is_manual_payment', true);
    }

    /**
     * Scope a query to only include automatic payments.
     */
    public function scopeOnline($query)
    {
        return $query->where('is_manual_payment', false);
    }

    /**
     * Get the status display attribute.
     */
    public function getStatusDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get the method display attribute.
     */
    public function getMethodDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->method));
    }

    /**
     * Check if the payment can be cancelled.
     */
    public function getCanRefundAttribute()
    {
        return !in_array($this->status, ['pending', 'unpaid']);
    }

    /**
     * Mark payment as paid.
     */
    public function markAsPaid($transactionId = null, $referenceId = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark payment as failed.
     */
    public function markAsRefunded($details = null)
    {
        $this->update([
            'status' => 'refunded',
            'details' => $details ?? $this->details,
        ]);

        return $this;
    }

    /**
     * Mark payment as unpaid.
     */
    public function markAsUnpaid($details = null)
    {
        $this->update([
            'status' => 'unpaid',
            'details' => $details ?? $this->details,
        ]);

        return $this;
    }

    /**
     * Generate a reference ID if not provided.
     */
    public static function generateReferenceId($payable_type = null)
    {
        return match($payable_type) {
            CarInspection::class => 'CIP-' . strtoupper(uniqid()),
            CarReservation::class => 'CRS-' . strtoupper(uniqid()),
            AuctionInvoice::class => 'AIN-' . strtoupper(uniqid()),
            UserInsuranceDeposit::class => 'AID-' . strtoupper(uniqid()),
            default => 'PAY-' . strtoupper(uniqid()),
        };
    }

    /**
     * Check if payment is for auction invoice
     */
    public function isAuctionInvoicePayment(): bool
    {
        return $this->payable_type === AuctionInvoice::class;
    }

    /**
     * Check if payment is for insurance deposit
     */
    public function isInsuranceDepositPayment(): bool
    {
        return $this->payable_type === UserInsuranceDeposit::class;
    }

    /** Get status badge class */
     public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            PaymentStatusEnum::PENDING => 'badge-warning',
            PaymentStatusEnum::PAID => 'badge-success',
            PaymentStatusEnum::CANCELLED => 'badge-danger',
            PaymentStatusEnum::REFUNDED => 'badge-info',
            default => 'badge-light',
        };
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->reference_id) {
                $payment->reference_id = self::generateReferenceId($payment->payable_type);
            }
        });
    }
}
