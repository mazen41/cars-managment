<?php

namespace App\Services;

use App\Models\AuctionItem;
use App\Models\AuctionOffer;
use App\Models\User;
use App\Models\AuctionAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Events\OfferReceived;
use App\Events\OfferAccepted;
use App\Events\OfferRejected;
use App\Jobs\ProcessOfferAcceptanceJob;

class AuctionOfferService
{
    /**
     * Create a new offer on an auction item
     *
     * @param AuctionItem $item
     * @param User $buyer
     * @param float $amount
     * @param string|null $message
     * @return AuctionOffer
     */
    public function createOffer(AuctionItem $item, User $buyer, float $amount, ?string $message): AuctionOffer
    {
        return DB::transaction(function () use ($item, $buyer, $amount, $message) {
            $offer = AuctionOffer::create([
                'auction_item_id' => $item->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $item->seller_id,
                'amount' => $amount,
                'status' => 'pending',
                'message' => $message,
                'expires_at' => null, // Will be set when auction starts
            ]);

            // Update Auction item's starting price for highest offer
            if ($amount > $item->starting_price) {
                $item->update(['starting_price' => $amount]);
            }
            // Log the offer creation
            AuctionAuditLog::create([
                'auction_room_id' => $item->auction_room_id,
                'auction_item_id' => $item->id,
                'user_id' => $buyer->id,
                'action' => 'offer_created',
                'details' => [
                    'offer_id' => $offer->id,
                    'amount' => $amount,
                    'message' => $message,
                ],
                'ip_address' => request()->ip(),
            ]);

            // Notify the seller
            $this->notifySeller($offer);

            return $offer;
        });
    }

    /**
     * Accept an offer
     *
     * @param AuctionOffer $offer
     * @param User $user
     * @return bool
     */
    public function acceptOffer(AuctionOffer $offer, User $user, string $response = null): bool
    {
        return DB::transaction(function () use ($offer, $user, $response) {
            if (!$offer->canBeAccepted()) {
                return false;
            }

            $offer->update([
                'status' => 'accepted',
                'seller_response' => $response,
                'responded_at' => now(),
            ]);

             // Dispatch the job to process the offer acceptance
            ProcessOfferAcceptanceJob::dispatch($offer->id);

            // Broadcast the OfferAccepted event
            broadcast(new OfferAccepted($offer))->toOthers();

            return true;
        });
    }

    /**
     * Reject an offer
     *
     * @param AuctionOffer $offer
     * @param User $seller
     * @param string $reason
     * @return bool
     */
    public function rejectOffer(AuctionOffer $offer, User $user, string $reason): bool
    {
        return DB::transaction(function () use ($offer, $user, $reason) {

            $offer->update([
                'status' => 'rejected',
                'seller_response' => $reason,
                'responded_at' => now(),
            ]);

            // Log the rejection
            AuctionAuditLog::create([
                'auction_room_id' => $offer->auctionItem->auction_room_id,
                'auction_item_id' => $offer->auction_item_id,
                'user_id' => $user->id,
                'action' => 'offer_rejected',
                'details' => [
                    'offer_id' => $offer->id,
                    'buyer_id' => $offer->buyer_id,
                    'amount' => $offer->amount,
                    'reason' => $reason,
                ],
                'ip_address' => request()->ip(),
            ]);

            // Broadcast the OfferRejected event
            broadcast(new OfferRejected($offer))->toOthers();

            return true;
        });
    }

    /**
     * Withdraw an offer
     *
     * @param AuctionOffer $offer
     * @param User $buyer
     * @return bool
     */
    public function withdrawOffer(AuctionOffer $offer, User $buyer): bool
    {
        return DB::transaction(function () use ($offer, $buyer) {
            if (!$offer->canBeWithdrawn()) {
                return false;
            }

            // Verify buyer owns the offer
            if ($offer->buyer_id !== $buyer->id) {
                return false;
            }

            $offer->update([
                'status' => 'withdrawn',
            ]);

            // Log the withdrawal
            AuctionAuditLog::create([
                'auction_room_id' => $offer->auctionItem->auction_room_id,
                'auction_item_id' => $offer->auction_item_id,
                'user_id' => $buyer->id,
                'action' => 'offer_withdrawn',
                'details' => [
                    'offer_id' => $offer->id,
                    'amount' => $offer->amount,
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Expire offers for an auction item
     *
     * @param AuctionItem $item
     * @return int
     */
    public function expireOffers(AuctionItem $item): int
    {
        return DB::transaction(function () use ($item) {
            $expiredCount = AuctionOffer::where('auction_item_id', $item->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'expired',
                    'expires_at' => now(),
                ]);

            if ($expiredCount > 0) {
                // Log the expiration
                AuctionAuditLog::create([
                    'auction_room_id' => $item->auction_room_id,
                    'auction_item_id' => $item->id,
                    'user_id' => null,
                    'action' => 'offers_expired',
                    'details' => [
                        'expired_count' => $expiredCount,
                        'reason' => 'auction_started',
                    ],
                    'ip_address' => request()->ip(),
                ]);
            }

            return $expiredCount;
        });
    }

    /**
     * Notify the seller about a new offer
     *
     * @param AuctionOffer $offer
     * @return void
     */
    public function notifySeller(AuctionOffer $offer): void
    {
        // Broadcast the OfferReceived event
        broadcast(new OfferReceived($offer))->toOthers();

        \Log::info('Seller notification for offer', [
            'offer_id' => $offer->id,
            'seller_id' => $offer->seller_id,
            'buyer_id' => $offer->buyer_id,
            'amount' => $offer->amount,
        ]);
    }
}
