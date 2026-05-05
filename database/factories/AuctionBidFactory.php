<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuctionBid>
 */
class AuctionBidFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AuctionBid::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 25000),
            'bid_time' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'is_winning' => false,
            'is_valid' => true,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }

    /**
     * Indicate that the bid is winning.
     */
    public function winning(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_winning' => true,
        ]);
    }

    /**
     * Indicate that the bid is invalid.
     */
    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => false,
            'is_winning' => false,
        ]);
    }

    /**
     * Indicate that the bid is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'bid_time' => $this->faker->dateTimeBetween('-5 minutes', 'now'),
        ]);
    }

    /**
     * Set a specific bid amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Set a specific auction.
     */
    public function forAuction(Auction $auction): static
    {
        return $this->state(fn (array $attributes) => [
            'auction_id' => $auction->id,
        ]);
    }

    /**
     * Set a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}