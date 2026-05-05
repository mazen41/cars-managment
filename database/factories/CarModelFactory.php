<?php

namespace Database\Factories;

use App\Models\CarBrand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CarModel>
 */
class CarModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Camry', 'Corolla', 'Prius', 'RAV4', 'Highlander', 'Tacoma',
                'Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey', 'Ridgeline',
                'F-150', 'Mustang', 'Explorer', 'Escape', 'Edge', 'Expedition',
                'Silverado', 'Malibu', 'Equinox', 'Tahoe', 'Suburban', 'Camaro',
                '3 Series', '5 Series', 'X3', 'X5', 'X7', 'i3', 'i8',
                'C-Class', 'E-Class', 'S-Class', 'GLC', 'GLE', 'GLS',
                'A3', 'A4', 'A6', 'Q3', 'Q5', 'Q7', 'Q8',
                'Golf', 'Jetta', 'Passat', 'Tiguan', 'Atlas', 'Arteon'
            ]),
            'brand_id' => CarBrand::factory(),
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the model is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the model is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Create a model for a specific brand.
     */
    public function forBrand(CarBrand $brand): static
    {
        return $this->state(fn (array $attributes) => [
            'brand_id' => $brand->id,
        ]);
    }
}