<?php

namespace App\Services;

use App\Enums\CarStatusEnum;
use App\Models\AuctionRoom;
use App\Models\AuctionItem;
use App\Models\Car;
use App\Models\AuctionAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuctionRoomService
{
    /**
     * Create a new auction room
     *
     * @param array $data
     * @return AuctionRoom
     */
    public function createRoom(array $data): AuctionRoom
    {
        return DB::transaction(function () use ($data) {
            $room = AuctionRoom::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'commission_percentage' => $data['commission_percentage'],
                'bid_increment_type' => $data['bid_increment_type'],
                'bid_increment_value' => $data['bid_increment_value'],
                'base_timer_seconds' => $data['base_timer_seconds'] ?? 60,
                'extension_seconds' => $data['extension_seconds'] ?? 30,
                //'insurance_deposit_amount' => $data['insurance_deposit_amount'],
                //'currency_id' => $data['currency_id'],
                'status' => 'draft',
                'scheduled_start_at' => $data['scheduled_start_at'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Log the creation
            AuctionAuditLog::create([
                'auction_room_id' => $room->id,
                'user_id' => Auth::id(),
                'action' => 'room_created',
                'details' => [
                    'room_name' => $room->name,
                    'commission_percentage' => $room->commission_percentage,
                ],
                'ip_address' => request()->ip(),
            ]);

            return $room;
        });
    }

    /**
     * Update an existing auction room
     *
     * @param AuctionRoom $room
     * @param array $data
     * @return AuctionRoom
     */
    public function updateRoom(AuctionRoom $room, array $data): AuctionRoom
    {
        return DB::transaction(function () use ($room, $data) {
            $oldData = $room->toArray();

            $room->update([
                'name' => $data['name'] ?? $room->name,
                'description' => $data['description'] ?? $room->description,
                'commission_percentage' => $data['commission_percentage'] ?? $room->commission_percentage,
                'bid_increment_type' => $data['bid_increment_type'] ?? $room->bid_increment_type,
                'bid_increment_value' => $data['bid_increment_value'] ?? $room->bid_increment_value,
                'base_timer_seconds' => $data['base_timer_seconds'] ?? $room->base_timer_seconds,
                'extension_seconds' => $data['extension_seconds'] ?? $room->extension_seconds,
                //'insurance_deposit_amount' => $data['insurance_deposit_amount'] ?? $room->insurance_deposit_amount,
                //'currency_id' => $data['currency_id'] ?? $room->currency_id,
                'scheduled_start_at' => $data['scheduled_start_at'] ?? $room->scheduled_start_at,
            ]);

            // Log the update
            AuctionAuditLog::create([
                'auction_room_id' => $room->id,
                'user_id' => Auth::id(),
                'action' => 'room_updated',
                'details' => [
                    'old_data' => $oldData,
                    'new_data' => $room->fresh()->toArray(),
                ],
                'ip_address' => request()->ip(),
            ]);

            return $room->fresh();
        });
    }

    /**
     * Add an item to an auction room
     *
     * @param AuctionRoom $room
     * @param Car $car
     * @param array $data
     * @return AuctionItem
     */
    public function addItemToRoom(AuctionRoom $room, Car $car, array $data): AuctionItem
    {
        return DB::transaction(function () use ($room, $car, $data) {
            // Get the next sequence order
            $maxSequence = $room->auctionItems()->max('sequence_order') ?? 0;

            $item = AuctionItem::create([
                'auction_room_id' => $room->id,
                'car_id' => $car->id,
                'seller_id' => $car->user_id,
                'sequence_order' => $maxSequence + 1,
                'starting_price' => $data['starting_price'],
                'reserve_price' => $data['reserve_price'] ?? null,
                'current_price' => null,
                'current_winner_id' => null,
                'status' => 'pending',
            ]);

            //update car availability status
            $car->update(['car_status' => CarStatusEnum::IN_AUCTION]);

            // Log the addition
            AuctionAuditLog::create([
                'auction_room_id' => $room->id,
                'auction_item_id' => $item->id,
                'user_id' => Auth::id(),
                'action' => 'item_added',
                'details' => [
                    'car_id' => $car->id,
                    'starting_price' => $item->starting_price,
                    'sequence_order' => $item->sequence_order,
                ],
                'ip_address' => request()->ip(),
            ]);

            return $item;
        });
    }

    /**
     * Remove an item from an auction room
     *
     * @param AuctionItem $item
     * @return bool
     */
    public function removeItemFromRoom(AuctionItem $item): bool
    {
        return DB::transaction(function () use ($item) {
            $roomId = $item->auction_room_id;
            $sequenceOrder = $item->sequence_order;

            // Log the removal
            AuctionAuditLog::create([
                'auction_room_id' => $roomId,
                'auction_item_id' => $item->id,
                'user_id' => Auth::id(),
                'action' => 'item_removed',
                'details' => [
                    'car_id' => $item->car_id,
                    'sequence_order' => $sequenceOrder,
                ],
                'ip_address' => request()->ip(),
            ]);

            // Delete the item
            $item->delete();

            // Reorder remaining items
            AuctionItem::where('auction_room_id', $roomId)
                ->where('sequence_order', '>', $sequenceOrder)
                ->decrement('sequence_order');

            return true;
        });
    }

    /**
     * Start an auction room
     *
     * @param AuctionRoom $room
     * @return bool
     */
    public function startRoom(AuctionRoom $room): bool
    {
        return DB::transaction(function () use ($room) {
            if (!$room->canStart()) {
                return false;
            }

            $room->update([
                'status' => 'active',
                'started_at' => now(),
            ]);

            // Log the start
            AuctionAuditLog::create([
                'auction_room_id' => $room->id,
                'user_id' => Auth::id(),
                'action' => 'room_started',
                'details' => [
                    'started_at' => $room->started_at,
                    'total_items' => $room->getTotalItems(),
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Set Auction Room as Scheduld
     * @param AuctionRoom $room
     * @return bool
     */
    public function setRoomScheduled(AuctionRoom $room): bool
    {
        return DB::transaction(function () use ($room) {
            if (!$room->canSchedul()) {
                return false;
            }

            $room->update([
                'status' => 'scheduled',
            ]);

            // Log the scheduling
            AuctionAuditLog::create([
                'auction_room_id' => $room->id,
                'user_id' => Auth::id(),
                'action' => 'room_scheduled',
                'details' => [
                    'scheduled_at' => now(),
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Cancel an auction room
     *
     * @param AuctionRoom $room
     * @param string $reason
     * @return bool
     */
    public function cancelRoom(AuctionRoom $room, string $reason): bool
    {
        return DB::transaction(function () use ($room, $reason) {
            $room->update([
                'status' => 'cancelled',
            ]);
            // Update cars status to 'available'

            foreach ($room->auctionItems as $item) {
                $item->car()->update(['car_status' => CarStatusEnum::AVAILABLE]);
            }

            // Log the cancellation
            AuctionAuditLog::create([
                'auction_room_id' => $room->id,
                'user_id' => Auth::id(),
                'action' => 'room_cancelled',
                'details' => [
                    'reason' => $reason,
                    'cancelled_at' => now(),
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Get statistics for an auction room
     *
     * @param AuctionRoom $room
     * @return array
     */
    public function getRoomStatistics(AuctionRoom $room): array
    {
        $items = $room->auctionItems;

        $totalItems = $items->count();
        $completedItems = $items->whereIn('status', ['sold', 'unsold'])->count();
        $soldItems = $items->where('status', 'sold')->count();
        $unsoldItems = $items->where('status', 'unsold')->count();

        $totalRevenue = $items->where('status', 'sold')->sum('current_price');
        $totalBids = $items->sum('total_bids');

        $averageSalePrice = $soldItems > 0
            ? $items->where('status', 'sold')->avg('current_price')
            : 0;

        $averageBidsPerItem = $completedItems > 0
            ? $totalBids / $completedItems
            : 0;

        return [
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
            'sold_items' => $soldItems,
            'unsold_items' => $unsoldItems,
            'active_item' => $room->getCurrentItem()?->id,
            'total_revenue' => $totalRevenue,
            'total_bids' => $totalBids,
            'average_sale_price' => round($averageSalePrice, 2),
            'average_bids_per_item' => round($averageBidsPerItem, 2),
            'completion_percentage' => $totalItems > 0
                ? round(($completedItems / $totalItems) * 100, 2)
                : 0,
        ];
    }
}
