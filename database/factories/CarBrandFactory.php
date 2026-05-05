<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CarBrand>
 */
class CarBrandFactory extends Factory
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
                'Toyota', 'Honda', 'Ford', 'Chevrolet', 'BMW', 'Mercedes-Benz',
                'Audi', 'Volkswagen', 'Nissan', 'Hyundai', 'Kia', 'Mazda',
                'Subaru', 'Lexus', 'Acura', 'Infiniti', 'Cadillac', 'Lincoln',
                'Jeep', 'Ram', 'GMC', 'Buick', 'Chrysler', 'Dodge'
            ]),
            'logo' => 1,
            'status' => 'active',
        ];
    }

    /**
     * Indicate that the brand is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}