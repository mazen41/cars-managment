<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionListingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'seller_id',
        'requested_starting_price',
        'requested_reserve_price',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'requested_starting_price' => 'decimal:2',
        'requested_reserve_price' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the car for this listing request.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the seller who submitted this request.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the admin who reviewed this request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Approve this listing request.
     */
    public function approve(User $admin, ?string $notes = null): void
    {
        $this->status = 'approved';
        $this->reviewed_by = $admin->id;
        $this->reviewed_at = now();
        if ($notes) {
            $this->admin_notes = $notes;
        }
        $this->save();
    }

    /**
     * Reject this listing request.
     */
    public function reject(User $admin, string $reason): void
    {
        $this->status = 'rejected';
        $this->reviewed_by = $admin->id;
        $this->reviewed_at = now();
        $this->admin_notes = $reason;
        $this->save();
    }
}
