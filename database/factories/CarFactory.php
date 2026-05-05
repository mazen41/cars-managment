<?php

namespace Database\Factories;

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarColor;
use App\Models\User;
use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get or create a color to avoid unique constraint violations
        $colorData = [
            ['name' => 'White', 'hex_code' => '#FFFFFF'],
            ['name' => 'Black', 'hex_code' => '#000000'],
            ['name' => 'Silver', 'hex_code' => '#C0C0C0'],
            ['name' => 'Red', 'hex_code' => '#FF0000'],
            ['name' => 'Blue', 'hex_code' => '#0000FF'],
        ];
        
        $randomColor = $this->faker->randomElement($colorData);
        $color = CarColor::firstOrCreate(
            ['name' => $randomColor['name']],
            ['hex_code' => $randomColor['hex_code'], 'status' => 'active']
        );

        return [
            'description' => $this->faker->paragraph(2),
            'model_id' => CarModel::factory(),
            'brand_id' => CarBrand::factory(),
            'color_id' => $color->id,
            'condition' => $this->faker->randomElement(['new', 'used']),
            'milage' => $this->faker->randomFloat(2, 0, 200000),
            'manufacture_year' => $this->faker->numberBetween(1990, 2024),
            'transmission' => $this->faker->randomElement(['manual', 'automatic', 'cvt']),
            'fuel_type' => $this->faker->randomElement(['gasoline', 'diesel', 'hybrid', 'electric']),
            'location' => $this->faker->city,
            'user_id' => User::factory(),
            'moderation_status' => CarModerationStatusEnum::PUBLISHED,
            'car_status' => CarStatusEnum::AVAILABLE,
            'price' => $this->faker->randomFloat(2, 5000, 100000),
        ];
    }

    /**
     * Indicate that the car is pending moderation.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => CarModerationStatusEnum::PENDING,
        ]);
    }

    /**
     * Indicate that the car is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'moderation_status' => CarModerationStatusEnum::REJECTED,
        ]);
    }

    /**
     * Indicate that the car is reserved.
     */
    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'car_status' => CarStatusEnum::RESERVED,
        ]);
    }

    /**
     * Indicate that the car is sold.
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'car_status' => CarStatusEnum::SOLD,
        ]);
    }

    /**
     * Indicate that the car is in auction.
     */
    public function inAuction(): static
    {
        return $this->state(fn (array $attributes) => [
            'car_status' => CarStatusEnum::IN_AUCTION,
        ]);
    }

    /**
     * Legacy method for backward compatibility - deprecated
     */
    public function draft(): static
    {
        return $this->pending();
    }

    /**
     * Indicate that the car is new.
     */
    public function new_condition(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'new',
            'milage' => 0,
        ]);
    }

    /**
     * Indicate that the car is used.
     */
    public function used_condition(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'used',
            'milage' => $this->faker->randomFloat(2, 1000, 200000),
        ]);
    }

    /**
     * Create a car with specific brand and model.
     */
    public function withBrandAndModel(CarBrand $brand, CarModel $model): static
    {
        return $this->state(fn (array $attributes) => [
            'brand_id' => $brand->id,
            'model_id' => $model->id,
        ]);
    }

    /**
     * Create a car with a specific color.
     */
    public function withColor(CarColor $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color_id' => $color->id,
        ]);
    }
}
