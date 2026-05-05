<?php

namespace Database\Factories;

use App\Models\AuctionItem;
use App\Models\AuctionRoom;
use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionItemFactory extends Factory
{
    protected $model = AuctionItem::class;
    
    protected static $sequenceCounters = [];

    public function definition(): array
    {
        return [
            'auction_room_id' => AuctionRoom::factory(),
            'car_id' => Car::factory(),
            'seller_id' => User::factory(),
            'sequence_order' => 1,
            'starting_price' => 1000.00,
            'reserve_price' => null,
            'current_price' => null,
            'current_winner_id' => null,
            'status' => 'pending',
            'started_at' => null,
            'ends_at' => null,
            'finalized_at' => null,
            'total_bids' => 0,
            'total_extensions' => 0,
        ];
    }
    
    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (AuctionItem $item) {
            // If auction_room_id is set, calculate the next sequence order
            if ($item->auction_room_id) {
                $maxSequence = AuctionItem::where('auction_room_id', $item->auction_room_id)
                    ->max('sequence_order') ?? 0;
                $item->sequence_order = $maxSequence + 1;
            }
        });
    }
}
