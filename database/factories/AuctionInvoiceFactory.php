<?php

namespace Database\Factories;

use App\Models\AuctionInvoice;
use App\Models\AuctionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionInvoiceFactory extends Factory
{
    protected $model = AuctionInvoice::class;

    public function definition(): array
    {
        return [
            'auction_item_id' => AuctionItem::factory()->create()->id,
            'invoice_type' => 'buyer_payment',
            'user_id' => User::factory()->create()->id,
            'amount' => $this->faker->randomFloat(2, 1000, 10000),
            'commission_amount' => null,
            'net_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'payment_id' => null,
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'paid_at' => null,
        ];
    }
}
