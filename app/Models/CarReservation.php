<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class CarReservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'car_id',
        'user_id',
        'status',
        'reserved_at',
        'notes',
        'admin_notes',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_COMPLETED = 'completed';


     /**
     * Get the payment for this inspection
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }


    /**
     * Get the car that is being reserved
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the user who made the reservation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who cancelled the reservation
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
    /**
     * Commission
     * @return MorphOne<Commission, CarReservation>
     */
    public function commission(): MorphOne
    {
        return $this->morphOne(Commission::class, 'commissionable');
    }

    // Scopes

    /**
     * Scope a query to only include active reservations
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope a query to only include pending reservations
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include confirmed reservations
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope a query to only include cancelled reservations
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope a query to only include expired reservations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere(function ($q) {
                        $q->where('expires_at', '<', now())
                          ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
                    });
    }

    /**
     * Scope a query to filter by payment status
     */
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->whereHas('payment', function($q) use($paymentStatus){
            return $q->where('status', $paymentStatus);
        });
    }

    /**
     * Scope a query to filter by car
     */
    public function scopeByCar($query, $carId)
    {
        return $query->where('car_id', $carId);
    }

    /**
     * Scope a query to filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors & Mutators

    /**
     * Get formatted reservation amount
     */
    public function getFormattedReservationAmountAttribute()
    {
        return $this->payment ? single_price($this->payment->amount) : null;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_CONFIRMED => 'badge-success',
            self::STATUS_CANCELLED => 'badge-danger',
            self::STATUS_EXPIRED => 'badge-secondary',
            self::STATUS_COMPLETED => 'badge-info',
            default => 'badge-light',
        };
    }

    /**
     * Check if reservation is active
     */
    public function getIsActiveAttribute()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if reservation can be cancelled
     */
    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if reservation can be cancelled
     */
    public function getCanBeConfirmedAttribute()
    {
        return in_array($this->status, [self::STATUS_PENDING]) && $this->payment && $this->payment->status === PaymentStatusEnum::PAID;
    }

    /**
     * Check if reservation can be marked as sold
     */
    public function getCanBeMarkedAsSoldAttribute()
    {
        return in_array($this->status, [self::STATUS_CONFIRMED]) && $this->payment && $this->payment->status === PaymentStatusEnum::PAID;
    }

    // Helper methods

    /**
     * Confirm the reservation
     */
    public function confirm($adminNotes = null)
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'admin_notes' => $adminNotes,
        ]);

        // Update car status to reserved
        $this->car->update(['car_status' => 'reserved']);

        return $this;
    }

    /**
     * Cancel the reservation
     */
    public function cancel($reason = null, $cancelledBy = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Complete the reservation (car sold)
     */
    public function complete($adminNotes = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'admin_notes' => $adminNotes,
        ]);

        // Update car status to sold
        $this->car->update(['car_status' => 'sold']);

        return $this;
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus($status, $transactionId = null, $paymentDetails = null)
    {
        $updateData = ['status' => $status];

        if ($transactionId) {
            $updateData['transaction_id'] = $transactionId;
        }

        if ($paymentDetails) {
            $updateData['payment_details'] = array_merge($this->payment_details ?? [], $paymentDetails);
        }

        $this->payment()->updateOrCreate([],$updateData);

        return $this;
    }

    /**
     * Get all available statuses
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }
}
