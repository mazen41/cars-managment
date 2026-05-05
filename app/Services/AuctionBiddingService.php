<?php

namespace App\Services;

use App\Models\AuctionItem;
use App\Models\Bid;
use App\Models\User;
use App\Models\AuctionAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuctionBiddingService
{
    /**
     * Place a bid on an auction item
     *
     * @param AuctionItem $item
     * @param User $bidder
     * @param float $amount
     * @param string $token
     * @return Bid
     */
    public function placeBid(AuctionItem $item, User $bidder, float $amount, string $token): Bid
    {
        // Check for existing bid with same token (idempotency)
        $existingBid = Bid::where('bid_token', $token)
            ->where('bidder_id', $bidder->id)
            ->first();

        if ($existingBid) {
            return $existingBid;
        }

        // Create new bid with pending status
        $bid = Bid::create([
            'auction_item_id' => $item->id,
            'bidder_id' => $bidder->id,
            'amount' => $amount,
            'status' => 'pending',
            'bid_token' => $token,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $bid;
    }

    /**
     * Validate a bid before processing
     *
     * @param AuctionItem $item
     * @param User $bidder
     * @param float $amount
     * @return array
     */
    public function validateBid(AuctionItem $item, User $bidder, float $amount): array
    {
        $errors = [];

        // Check if item is active
        if (!$item->canReceiveBids()) {
            $errors[] = 'This item is not currently accepting bids';
        }

        // Check if user can bid (insurance deposit)
        if (!$bidder->canBid()) {
            $errors[] = 'Insurance deposit required';
        }

        // Check if bid amount meets minimum
        $minimumBid = $this->calculateMinimumBid($item);
        if ($amount < $minimumBid) {
            $errors[] = "Bid amount must be at least {$minimumBid}";
        }

        // Check if bidder is not the seller
        if ($bidder->id === $item->seller_id) {
            $errors[] = 'You cannot bid on your own item';
        }

        return [
            'valid' => empty($errors),
            'reason' => empty($errors) ? null : implode(', ', $errors),
            'errors' => $errors,
            'minimum_bid' => $minimumBid,
        ];
    }

    /**
     * Process a bid with database locking
     *
     * @param Bid $bid
     * @return bool
     */
    public function processBid(Bid $bid): bool
    {
        return DB::transaction(function () use ($bid) {
            // Acquire row lock on the auction item
            $item = AuctionItem::where('id', $bid->auction_item_id)
                ->lockForUpdate()
                ->first();

            if (!$item) {
                $this->rejectBid($bid, 'Auction item not found');
                return false;
            }

            // Validate the bid
            $validation = $this->validateBid($item, $bid->bidder, (float) $bid->amount);
            if (!$validation['valid']) {
                $this->rejectBid($bid, implode(', ', $validation['errors']));
                return false;
            }

            // Mark previous bids as outbid
            Bid::where('auction_item_id', $item->id)
                ->where('status', 'accepted')
                ->update(['status' => 'outbid']);

            // Update the bid status
            $bid->update(['status' => 'accepted']);

            // Update the auction item
            $item->update([
                'current_price' => $bid->amount,
                'current_winner_id' => $bid->bidder_id,
                'ends_at' => $this->extendTimer($item),
                'total_bids' => $item->total_bids + 1,
                'total_extensions' => $item->total_extensions + 1,
            ]);

            // Log the bid
            AuctionAuditLog::create([
                'auction_room_id' => $item->auction_room_id,
                'auction_item_id' => $item->id,
                'user_id' => $bid->bidder_id,
                'action' => 'bid_accepted',
                'details' => [
                    'bid_id' => $bid->id,
                    'amount' => $bid->amount,
                    'previous_price' => $item->current_price,
                    'new_ends_at' => $item->ends_at,
                ],
                'ip_address' => $bid->ip_address,
            ]);

            return true;
        });
    }

    /**
     * Calculate the minimum bid for an auction item
     *
     * @param AuctionItem $item
     * @return float
     */
    public function calculateMinimumBid(AuctionItem $item): float
    {
        $room = $item->auctionRoom;
        $currentPrice = $item->current_price ?? $item->starting_price;

        if ($room->bid_increment_type === 'percentage') {
            $increment = $currentPrice * ($room->bid_increment_value / 100);
        } else {
            $increment = $room->bid_increment_value;
        }

        return round($currentPrice + $increment, 2);
    }

    /**
     * Extend the timer for an auction item
     *
     * @param AuctionItem $item
     * @return Carbon
     */
    public function extendTimer(AuctionItem $item): Carbon
    {
        $room = $item->auctionRoom;
        $currentEndsAt = Carbon::parse($item->ends_at);

        // Add extension seconds to current ends_at
        return $currentEndsAt->addSeconds($room->extension_seconds);
    }

    /**
     * Reject a bid with a reason
     *
     * @param Bid $bid
     * @param string $reason
     * @return bool
     */
    public function rejectBid(Bid $bid, string $reason): bool
    {
        $bid->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        // Log the rejection
        AuctionAuditLog::create([
            'auction_item_id' => $bid->auction_item_id,
            'user_id' => $bid->bidder_id,
            'action' => 'bid_rejected',
            'details' => [
                'bid_id' => $bid->id,
                'amount' => $bid->amount,
                'reason' => $reason,
            ],
            'ip_address' => $bid->ip_address,
        ]);

        return true;
    }
}
