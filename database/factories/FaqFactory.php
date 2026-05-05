<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        $question = $this->faker->sentence() . '?';

        return [
            'type' => $this->faker->randomElement(['general', 'billing', 'technical', 'account']),
            'slug' => Str::slug($question) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'is_published' => $this->faker->boolean(80), // 80% chance of being published
            'sort_order' => $this->faker->numberBetween(0, 100),
            'view_count' => $this->faker->numberBetween(0, 1000),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => $this->faker->numberBetween(500, 2000),
        ]);
    }
}
