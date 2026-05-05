<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'commission_percentage',
        'bid_increment_type',
        'bid_increment_value',
        'base_timer_seconds',
        'extension_seconds',
        'insurance_deposit_amount',
        'currency_id',
        'status',
        'scheduled_start_at',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'bid_increment_value' => 'decimal:2',
        'base_timer_seconds' => 'integer',
        'extension_seconds' => 'integer',
        'insurance_deposit_amount' => 'decimal:2',
        'scheduled_start_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the auction items in this room.
     */
    public function auctionItems(): HasMany
    {
        return $this->hasMany(AuctionItem::class);
    }

    /**
     * Get the currency for this auction room.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the user who created this auction room.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias for creator relationship.
     */
    public function createdBy(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * Scope a query to only include active auction rooms.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include scheduled auction rooms.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope a query to only include completed auction rooms.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if the auction room is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the auction room can be started.
     */
    public function canStart(): bool
    {
        return in_array($this->status, ['draft', 'scheduled'])
            && $this->auctionItems()->pending()->count() > 0;
    }

    /**
     * Check if the auction room can be scheduled
     */
    public function canSchedul(): bool
    {
        return $this->status === 'draft'
        && !is_null($this->scheduled_start_at)
            && $this->auctionItems()->count() > 0;
    }

    /**
     * Get the next item in the auction sequence.
     */
    public function getNextItem(): ?AuctionItem
    {
        return $this->auctionItems()
         ->with('car')
            ->where('status', 'pending')
            ->orderBy('sequence_order')
            ->first();
    }

    /**
     * Get the currently active item in the auction.
     */
    public function getCurrentItem(): ?AuctionItem
    {
        return $this->auctionItems()
        ->with('car')
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get the total number of items in the auction room.
     */
    public function getTotalItems(): int
    {
        return $this->auctionItems()->count();
    }

    /**
     * Get the number of completed items (sold or unsold).
     */
    public function getCompletedItems(): int
    {
        return $this->auctionItems()
            ->whereIn('status', ['sold', 'unsold', 'offer_accepted'])
            ->count();
    }
}
