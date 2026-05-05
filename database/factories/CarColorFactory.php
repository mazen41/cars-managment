<?php

namespace Database\Factories;

use App\Models\CarColor;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarColorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $colors = [
            ['name' => 'White', 'hex_code' => '#FFFFFF'],
            ['name' => 'Black', 'hex_code' => '#000000'],
            ['name' => 'Silver', 'hex_code' => '#C0C0C0'],
            ['name' => 'Gray', 'hex_code' => '#808080'],
            ['name' => 'Red', 'hex_code' => '#FF0000'],
            ['name' => 'Blue', 'hex_code' => '#0000FF'],
            ['name' => 'Green', 'hex_code' => '#008000'],
            ['name' => 'Yellow', 'hex_code' => '#FFFF00'],
            ['name' => 'Orange', 'hex_code' => '#FFA500'],
            ['name' => 'Brown', 'hex_code' => '#A52A2A'],
            ['name' => 'Purple', 'hex_code' => '#800080'],
            ['name' => 'Pink', 'hex_code' => '#FFC0CB'],
            ['name' => 'Gold', 'hex_code' => '#FFD700'],
            ['name' => 'Beige', 'hex_code' => '#F5F5DC'],
            ['name' => 'Maroon', 'hex_code' => '#800000'],
            ['name' => 'Navy', 'hex_code' => '#000080'],
        ];

        // This static property will store the IDs of created colors.
        static $createdColors = [];

        // Select a random color from the list
        $randomColor = $this->faker->randomElement($colors);

        // Check if this color has already been created.
        $existingColor = CarColor::where('name', $randomColor['name'])->first();

        // If it exists, we return the existing color's ID.
        if ($existingColor) {
            return [
                'name' => $existingColor->name,
                'hex_code' => $existingColor->hex_code,
                'status' => $existingColor->status,
            ];
        }

        // If it doesn't exist, we create and return a new one.
        return [
            'name' => $randomColor['name'],
            'hex_code' => $randomColor['hex_code'],
            'status' => 'active',
        ];
    }
}