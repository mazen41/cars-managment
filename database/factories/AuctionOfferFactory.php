<?php

namespace Database\Factories;

use App\Models\AuctionOffer;
use App\Models\AuctionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionOfferFactory extends Factory
{
    protected $model = AuctionOffer::class;

    public function definition(): array
    {
        $item = AuctionItem::factory()->create();
        
        return [
            'auction_item_id' => $item->id,
            'buyer_id' => User::factory()->create()->id,
            'seller_id' => $item->seller_id,
            'amount' => $this->faker->randomFloat(2, 1000, 10000),
            'status' => 'pending',
            'message' => $this->faker->sentence(),
            'seller_response' => null,
            'responded_at' => null,
            'expires_at' => null,
        ];
    }
}
