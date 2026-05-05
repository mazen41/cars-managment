<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_item_id',
        'buyer_id',
        'seller_id',
        'amount',
        'status',
        'message',
        'seller_response',
        'responded_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the auction item this offer is for.
     */
    public function auctionItem(): BelongsTo
    {
        return $this->belongsTo(AuctionItem::class);
    }

    /**
     * Get the buyer who made this offer.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller who received this offer.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Scope a query to only include pending offers.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include offers for a specific item.
     */
    public function scopeForItem($query, AuctionItem $item)
    {
        return $query->where('auction_item_id', $item->id);
    }

    /**
     * Scope a query to only include offers by a specific seller.
     */
    public function scopeBySeller($query, User $seller)
    {
        return $query->where('seller_id', $seller->id);
    }

    /**
     * Scope a query to only include offers by a specific buyer.
     */
    public function scopeByBuyer($query, User $buyer)
    {
        return $query->where('buyer_id', $buyer->id);
    }

    /**
     * Check if the offer is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the offer can be accepted.
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'pending' 
            && $this->auctionItem->canReceiveOffers();
    }

    /**
     * Check if the offer can be withdrawn by the buyer.
     */
    public function canBeWithdrawn(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Expire this offer.
     */
    public function expire(): void
    {
        $this->status = 'expired';
        $this->save();
    }
}
