<?php

namespace Database\Factories;

use App\Models\AuctionRequest;
use App\Models\Car;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionRequestFactory extends Factory
{
    protected $model = AuctionRequest::class;

    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'seller_id' => User::factory()->create(['user_type' => 'customer'])->id,
            'requested_reserve_price' => $this->faker->randomFloat(2, 5000, 50000),
            'preferred_duration' => $this->faker->randomElement([24, 48, 72, 96, 120]), // hours
            'notes' => $this->faker->optional()->paragraph(),
            'status' => AuctionRequest::STATUS_PENDING,
            'admin_id' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ];
    }

    /**
     * Indicate that the auction request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionRequest::STATUS_APPROVED,
            'admin_id' => User::factory()->create(['user_type' => 'admin'])->id,
            'reviewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'review_notes' => $this->faker->optional()->sentence(),
        ]);
    }

    /**
     * Indicate that the auction request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionRequest::STATUS_REJECTED,
            'admin_id' => User::factory()->create(['user_type' => 'admin'])->id,
            'reviewed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'review_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the auction request is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AuctionRequest::STATUS_PENDING,
            'admin_id' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ]);
    }
}