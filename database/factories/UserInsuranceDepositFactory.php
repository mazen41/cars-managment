<?php

namespace Database\Factories;

use App\Models\UserInsuranceDeposit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserInsuranceDepositFactory extends Factory
{
    protected $model = UserInsuranceDeposit::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'amount' => 500.00,
            'status' => 'pending',
            'payment_id' => null,
            'refund_payment_id' => null,
            'paid_at' => null,
            'refunded_at' => null,
        ];
    }
}
