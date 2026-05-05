<?php

namespace Database\Factories;

use App\Models\CustomerProduct;
use App\Models\User;
use App\Models\Category;
use App\Models\State;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerProduct>
 */
class CustomerProductFactory extends Factory
{
    protected $model = CustomerProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $state = State::factory()->create();
        $city = City::factory()->create(['state_id' => $state->id]);

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'condition' => $this->faker->randomElement(['new', 'used']),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'category_id' => Category::factory(),
            'main_photo' => null,
            'photos' => json_encode([]),
            'address' => $this->faker->address(),
            'state_id' => $state->id,
            'city_id' => $city->id,
            'longitude' => $this->faker->longitude(),
            'latitude' => $this->faker->latitude(),
            'moderation_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'availability_status' => $this->faker->randomElement(['available', 'unavailable']),
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the product is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => 'approved',
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the product is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => 'pending',
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the product is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => 'rejected',
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the product is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_status' => 'available',
        ]);
    }

    /**
     * Indicate that the product is unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'availability_status' => 'unavailable',
        ]);
    }
}
