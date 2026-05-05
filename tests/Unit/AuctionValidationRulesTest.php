<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Rules\ValidAuctionTiming;
use App\Rules\ValidBidAmount;
use App\Rules\ValidCarForAuction;
use App\Rules\ValidReservePrice;
use App\Rules\ValidBidIncrement;
use App\Rules\ValidAuctionStatus;
use App\Models\Auction;
use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AuctionValidationRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_auction_timing_rule()
    {
        $rule = new ValidAuctionTiming(now()->addHour(), 1, 168);
        
        // Test valid end time
        $this->assertTrue($this->validateRule($rule, now()->addHours(2)));
        
        // Test invalid end time (too short)
        $this->assertFalse($this->validateRule($rule, now()->addMinutes(30)));
        
        // Test invalid end time (too long)
        $this->assertFalse($this->validateRule($rule, now()->addDays(8)));
    }

    public function test_valid_reserve_price_rule()
    {
        $rule = new ValidReservePrice(1000);
        
        // Test valid reserve price
        $this->assertTrue($this->validateRule($rule, 1500));
        
        // Test invalid reserve price (too low)
        $this->assertFalse($this->validateRule($rule, 50));
        
        // Test invalid reserve price (less than starting bid)
        $this->assertFalse($this->validateRule($rule, 500));
    }

    public function test_valid_bid_increment_rule()
    {
        $rule = new ValidBidIncrement(1000, 5000);
        
        // Test valid bid increment
        $this->assertTrue($this->validateRule($rule, 100));
        
        // Test invalid bid increment (too low)
        $this->assertFalse($this->validateRule($rule, 0));
        
        // Test invalid bid increment (too high compared to starting bid)
        $this->assertFalse($this->validateRule($rule, 600)); // More than 50% of starting bid
    }

    public function test_valid_bid_amount_rule()
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'status' => Auction::STATUS_ACTIVE,
            'current_bid' => 1000,
            'bid_increment' => 100,
            'end_time' => now()->addHour()
        ]);

        $rule = new ValidBidAmount($auction, $user->id);
        
        // Test valid bid amount
        $this->assertTrue($this->validateRule($rule, 1100));
        
        // Test invalid bid amount (too low)
        $this->assertFalse($this->validateRule($rule, 1050));
        
        // Test seller trying to bid on own auction
        $sellerRule = new ValidBidAmount($auction, $seller->id);
        $this->assertFalse($this->validateRule($sellerRule, 1100));
    }

    public function test_valid_auction_status_rule()
    {
        $auction = Auction::factory()->create([
            'status' => Auction::STATUS_PENDING,
            'start_time' => now()->subMinute(), // Start time in the past
            'end_time' => now()->addHour()
        ]);
        $rule = new ValidAuctionStatus($auction);
        
        // Test valid status transition
        $this->assertTrue($this->validateRule($rule, Auction::STATUS_ACTIVE));
        
        // Test invalid status transition
        $this->assertFalse($this->validateRule($rule, Auction::STATUS_ENDED));
    }

    /**
     * Helper method to validate a rule
     */
    private function validateRule($rule, $value): bool
    {
        $passed = true;
        $fail = function($message) use (&$passed) {
            $passed = false;
        };
        
        $rule->validate('test_attribute', $value, $fail);
        
        return $passed;
    }
}