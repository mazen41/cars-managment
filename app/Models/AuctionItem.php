<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AuctionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_room_id',
        'car_id',
        'seller_id',
        'sequence_order',
        'starting_price',
        'reserve_price',
        'current_price',
        'current_winner_id',
        'status',
        'started_at',
        'ends_at',
        'finalized_at',
        'total_bids',
        'total_extensions',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'starting_price' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'current_price' => 'decimal:2',
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'finalized_at' => 'datetime',
        'total_bids' => 'integer',
        'total_extensions' => 'integer',
    ];

    /**
     * Get the auction room this item belongs to.
     */
    public function auctionRoom(): BelongsTo
    {
        return $this->belongsTo(AuctionRoom::class);
    }

    /**
     * Get the car being auctioned.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the seller of this auction item.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the current winning bidder.
     */
    public function currentWinner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_winner_id');
    }

    /**
     * Get all bids for this auction item.
     */
    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    /**
     * Get all offers for this auction item.
     */
    public function auctionOffers(): HasMany
    {
        return $this->hasMany(AuctionOffer::class);
    }

    /**
     * Get the buyer payment invoice for this item.
     */
    public function buyerPayment(): HasOne
    {
        return $this->hasOne(AuctionInvoice::class)->where('invoice_type', 'buyer_payment');
    }

    /**
     * Get the seller payout invoice for this item.
     */
    public function sellerPayout(): HasOne
    {
        return $this->hasOne(AuctionInvoice::class)->where('invoice_type', 'seller_payout');
    }

    /**
     * Scope a query to only include active auction items.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include pending auction items.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include sold auction items.
     */
    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    /**
     * Scope a query to only include unsold auction items.
     */
    public function scopeUnsold($query)
    {
        return $query->where('status', 'unsold');
    }

    /**
     * Scope a query to only include items in a specific room.
     */
    public function scopeInRoom($query, AuctionRoom $room)
    {
        return $query->where('auction_room_id', $room->id);
    }

    /**
     * Check if the auction item is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the item has a reserve price set.
     */
    public function hasReservePrice(): bool
    {
        return $this->reserve_price !== null && $this->reserve_price > 0;
    }

    /**
     * Check if the current price meets the reserve price.
     */
    public function meetsReserve(): bool
    {
        if (!$this->hasReservePrice()) {
            return true;
        }

        return $this->current_price >= $this->reserve_price;
    }

    /**
     * Get the minimum bid amount for this item.
     */
    public function getMinimumBid(): float
    {
        $basePrice = $this->current_price ?? $this->starting_price;
        $room = $this->auctionRoom;

        if ($room->bid_increment_type === 'percentage') {
            $increment = $basePrice * ($room->bid_increment_value / 100);
        } else {
            $increment = $room->bid_increment_value;
        }

        return (float) ($basePrice + $increment);
    }

    /**
     * Get the number of seconds remaining in the auction.
     */
    public function getSecondsRemaining(): int
    {
        if (!$this->ends_at) {
            return 0;
        }

        $remaining = Carbon::now()->diffInSeconds($this->ends_at, false);
        return max(0, (int) $remaining);
    }

    /**
     * Check if the item can receive bids.
     */
    public function canReceiveBids(): bool
    {
        return $this->status === 'active' && $this->getSecondsRemaining() > 0;
    }

    /**
     * Check if the item can receive offers.
     */
    public function canReceiveOffers(): bool
    {
        return in_array($this->status, ['pending', 'withdrawn']);
    }

    /**
     * Extend the auction timer by the configured extension seconds.
     */
    public function extendTimer(): void
    {
        if (!$this->ends_at) {
            return;
        }

        $extensionSeconds = $this->auctionRoom->extension_seconds;
        $this->ends_at = $this->ends_at->addSeconds($extensionSeconds);
        $this->total_extensions++;
        $this->save();
    }
}
