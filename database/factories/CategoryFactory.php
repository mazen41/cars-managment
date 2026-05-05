<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => 0,
            'level' => 0,
            'name' => $this->faker->words(2, true),
            'order_level' => $this->faker->numberBetween(1, 100),
            'commision_rate' => $this->faker->randomFloat(2, 0, 20),
            'banner' => null,
            'icon' => null,
            'cover_image' => null,
            'featured' => $this->faker->boolean() ? 1 : 0,
            'top' => $this->faker->boolean() ? 1 : 0,
            'digital' => $this->faker->boolean() ? 1 : 0,
            'slug' => $this->faker->slug(),
            'meta_title' => $this->faker->sentence(),
            'meta_description' => $this->faker->paragraph(),
        ];
    }
}
