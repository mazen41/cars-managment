<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionAuditLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // Audit logs don't need updated_at

    protected $fillable = [
        'auction_room_id',
        'auction_item_id',
        'user_id',
        'action',
        'details',
        'ip_address',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Get the auction room this log entry is for.
     */
    public function auctionRoom(): BelongsTo
    {
        return $this->belongsTo(AuctionRoom::class);
    }

    /**
     * Get the auction item this log entry is for.
     */
    public function auctionItem(): BelongsTo
    {
        return $this->belongsTo(AuctionItem::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include logs for a specific room.
     */
    public function scopeForRoom($query, AuctionRoom $room)
    {
        return $query->where('auction_room_id', $room->id);
    }

    /**
     * Scope a query to only include logs for a specific item.
     */
    public function scopeForItem($query, AuctionItem $item)
    {
        return $query->where('auction_item_id', $item->id);
    }

    /**
     * Scope a query to only include logs for a specific action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
