<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_item_id',
        'bidder_id',
        'amount',
        'status',
        'rejection_reason',
        'bid_token',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the auction item this bid belongs to.
     */
    public function auctionItem(): BelongsTo
    {
        return $this->belongsTo(AuctionItem::class);
    }

    /**
     * Get the bidder who placed this bid.
     */
    public function bidder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bidder_id');
    }

    /**
     * Scope a query to only include accepted bids.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include bids for a specific item.
     */
    public function scopeForItem($query, AuctionItem $item)
    {
        return $query->where('auction_item_id', $item->id);
    }

    /**
     * Scope a query to only include bids by a specific bidder.
     */
    public function scopeByBidder($query, User $bidder)
    {
        return $query->where('bidder_id', $bidder->id);
    }

    /**
     * Check if the bid was accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if the bid was rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the bid was outbid by another bidder.
     */
    public function isOutbid(): bool
    {
        return $this->status === 'outbid';
    }

    /**
     * Mark this bid as outbid.
     */
    public function markAsOutbid(): void
    {
        $this->status = 'outbid';
        $this->save();
    }
}
