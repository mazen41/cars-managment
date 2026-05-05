<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'name' => 'US Dollar',
            'symbol' => '$',
            'code' => 'USD',
            'exchange_rate' => 1.00,
            'status' => 1,
        ];
    }

    public function euro(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Euro',
            'symbol' => '€',
            'code' => 'EUR',
            'exchange_rate' => 0.85,
        ]);
    }

    public function gbp(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'British Pound',
            'symbol' => '£',
            'code' => 'GBP',
            'exchange_rate' => 0.73,
        ]);
    }
}
