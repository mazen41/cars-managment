<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Auction>
 */
class AuctionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Auction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('-1 week', '+1 week');
        $endTime = $this->faker->dateTimeBetween($startTime, $startTime->format('Y-m-d H:i:s') . ' +7 days');
        
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'car_id' => Car::factory(),
            'seller_id' => User::factory(),
            'admin_id' => User::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'reserve_price' => $this->faker->randomFloat(2, 5000, 50000),
            'starting_bid' => $this->faker->randomFloat(2, 1000, 10000),
            'current_bid' => 0,
            'current_bidder_id' => null,
            'bid_increment' => $this->faker->randomElement([50, 100, 250, 500]),
            'status' => Auction::STATUS_PENDING,
            'auto_extend_enabled' => $this->faker->boolean(80), // 80% chance of being enabled
            'extend_duration' => $this->faker->randomElement([60, 120, 300]), // 1, 2, or 5 minutes
            'max_extensions' => $this->faker->numberBetween(3, 10),
            'extensions_count' => 0,
            'winner_id' => null,
            'final_price' => null,
        ];
    }

    /**
     * Indicate that the auction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Auction::STATUS_PENDING,
            'start_time' => $this->faker->dateTimeBetween('+1 hour', '+1 week'),
        ]);
    }

    /**
     * Indicate that the auction is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Auction::STATUS_ACTIVE,
            'start_time' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
            'end_time' => $this->faker->dateTimeBetween('+1 hour', '+1 week'),
        ]);
    }

    /**
     * Indicate that the auction has ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Auction::STATUS_ENDED,
            'start_time' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
            'end_time' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
        ]);
    }

    /**
     * Indicate that the auction is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Auction::STATUS_CANCELLED,
        ]);
    }

    /**
     * Indicate that the auction has bids.
     */
    public function withBids(): static
    {
        return $this->state(function (array $attributes) {
            $currentBid = $this->faker->randomFloat(2, $attributes['starting_bid'] ?? 1000, 25000);
            return [
                'current_bid' => $currentBid,
                'current_bidder_id' => User::factory(),
            ];
        });
    }

    /**
     * Indicate that the auction has a winner.
     */
    public function withWinner(): static
    {
        return $this->state(function (array $attributes) {
            $finalPrice = $this->faker->randomFloat(2, $attributes['starting_bid'] ?? 1000, 25000);
            $winner = User::factory();
            
            return [
                'status' => Auction::STATUS_ENDED,
                'current_bid' => $finalPrice,
                'current_bidder_id' => $winner,
                'winner_id' => $winner,
                'final_price' => $finalPrice,
                'end_time' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
            ];
        });
    }

    /**
     * Indicate that the auction reserve is met.
     */
    public function reserveMet(): static
    {
        return $this->state(function (array $attributes) {
            $reservePrice = $attributes['reserve_price'] ?? 5000;
            $currentBid = $this->faker->randomFloat(2, $reservePrice, $reservePrice * 2);
            
            return [
                'current_bid' => $currentBid,
                'current_bidder_id' => User::factory(),
            ];
        });
    }

    /**
     * Indicate that the auction is ending soon.
     */
    public function endingSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Auction::STATUS_ACTIVE,
            'start_time' => $this->faker->dateTimeBetween('-1 week', '-1 hour'),
            'end_time' => $this->faker->dateTimeBetween('+1 minute', '+10 minutes'),
        ]);
    }

    /**
     * Indicate that the auction has been extended.
     */
    public function extended(): static
    {
        return $this->state(fn (array $attributes) => [
            'extensions_count' => $this->faker->numberBetween(1, 3),
            'auto_extend_enabled' => true,
        ]);
    }
}
