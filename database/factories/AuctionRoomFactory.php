<?php

namespace Database\Factories;

use App\Models\AuctionRoom;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionRoomFactory extends Factory
{
    protected $model = AuctionRoom::class;

    public function definition(): array
    {
        // Get or create a currency
        $currency = Currency::first();
        if (!$currency) {
            $currency = Currency::create([
                'name' => 'US Dollar',
                'symbol' => '$',
                'code' => 'USD',
                'exchange_rate' => 1.00,
                'status' => 1,
            ]);
        }

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'commission_percentage' => 10.00,
            'bid_increment_type' => 'flat',
            'bid_increment_value' => 50.00,
            'base_timer_seconds' => 60,
            'extension_seconds' => 30,
            'insurance_deposit_amount' => 500.00,
            'currency_id' => $currency->id,
            'status' => 'draft',
            'scheduled_start_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'created_by' => User::factory()->create()->id,
        ];
    }
}
