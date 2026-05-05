<?php

namespace Database\Factories;

use App\Models\Bid;
use App\Models\AuctionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        return [
            'auction_item_id' => AuctionItem::factory(),
            'bidder_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'status' => 'pending',
            'rejection_reason' => null,
            'bid_token' => Str::uuid()->toString(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    /**
     * Indicate that the bid is accepted.
     */
    public function accepted()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'accepted',
            ];
        });
    }

    /**
     * Indicate that the bid is rejected.
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'rejection_reason' => 'Bid amount too low',
            ];
        });
    }

    /**
     * Indicate that the bid is outbid.
     */
    public function outbid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'outbid',
            ];
        });
    }
}
