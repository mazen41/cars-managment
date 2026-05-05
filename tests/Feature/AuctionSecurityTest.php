<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\User;
use App\Models\Car;
use App\Services\BidValidationService;
use App\Services\AuctionFraudDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuctionSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected BidValidationService $bidValidationService;
    protected AuctionFraudDetectionService $fraudDetectionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bidValidationService = app(BidValidationService::class);
        $this->fraudDetectionService = app(AuctionFraudDetectionService::class);
    }

    /** @test */
    public function it_prevents_seller_from_bidding_on_own_auction()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'active'
        ]);

        $validation = $this->bidValidationService->validateBid($auction, $seller, 1000);

        $this->assertFalse($validation['valid']);
        $this->assertContains('Sellers cannot bid on their own auctions', $validation['errors']);
    }

    /** @test */
    public function it_prevents_duplicate_bids()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $bidder = User::factory()->create(['user_type' => 'customer', 'email_verified_at' => now()]);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'active',
            'current_bid' => 500
        ]);

        // Create an existing bid
        $auction->bids()->create([
            'user_id' => $bidder->id,
            'amount' => 1000,
            'bid_time' => now(),
            'is_valid' => true
        ]);

        $validation = $this->bidValidationService->validateBid($auction, $bidder, 1000);

        $this->assertFalse($validation['valid']);
        $this->assertContains('You have already placed a bid for this amount', $validation['errors']);
    }

    /** @test */
    public function it_enforces_minimum_bid_amount()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $bidder = User::factory()->create(['user_type' => 'customer', 'email_verified_at' => now()]);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'active',
            'current_bid' => 1000,
            'bid_increment' => 100
        ]);

        // Try to bid less than minimum required
        $validation = $this->bidValidationService->validateBid($auction, $bidder, 1050);

        $this->assertFalse($validation['valid']);
        $this->assertTrue(str_contains(implode(' ', $validation['errors']), 'must be at least'));
    }

    /** @test */
    public function it_detects_rapid_bidding_patterns()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $bidder = User::factory()->create(['user_type' => 'customer', 'email_verified_at' => now()]);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'active'
        ]);

        // Create multiple recent bids to simulate rapid bidding
        for ($i = 0; $i < 6; $i++) {
            $auction->bids()->create([
                'user_id' => $bidder->id,
                'amount' => 1000 + ($i * 100),
                'bid_time' => now()->subMinutes(4),
                'is_valid' => true
            ]);
        }

        $fraudAnalysis = $this->fraudDetectionService->analyzeBid($auction, $bidder, 2000);

        $this->assertGreaterThan(0, $fraudAnalysis['risk_score']);
        $this->assertTrue($fraudAnalysis['should_flag']);
    }

    /** @test */
    public function it_applies_rate_limiting()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $bidder = User::factory()->create(['user_type' => 'customer', 'email_verified_at' => now()]);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'active'
        ]);

        // Simulate rate limit being hit
        $userKey = "user_bid_rate:{$bidder->id}";
        Cache::put($userKey, 6, 60); // Exceed the limit of 5

        $validation = $this->bidValidationService->validateBid($auction, $bidder, 1000);

        $this->assertFalse($validation['valid']);
        $this->assertStringContainsString('Too many bids placed', implode(' ', $validation['errors']));
    }

    /** @test */
    public function it_validates_auction_status()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $bidder = User::factory()->create(['user_type' => 'customer', 'email_verified_at' => now()]);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'ended' // Auction has ended
        ]);

        $validation = $this->bidValidationService->validateBid($auction, $bidder, 1000);

        $this->assertFalse($validation['valid']);
        $this->assertContains('Auction is not active (current status: ended)', $validation['errors']);
    }

    /** @test */
    public function it_requires_email_verification()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $bidder = User::factory()->create([
            'user_type' => 'customer',
            'email_verified_at' => null // Not verified
        ]);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'active'
        ]);

        $validation = $this->bidValidationService->validateBid($auction, $bidder, 1000);

        $this->assertFalse($validation['valid']);
        $this->assertContains('Email verification required to place bids', $validation['errors']);
    }

    /** @test */
    public function it_validates_bid_timing()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $bidder = User::factory()->create(['user_type' => 'customer', 'email_verified_at' => now()]);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'active',
            'end_time' => now()->subMinutes(5) // Auction ended 5 minutes ago
        ]);

        $validation = $this->bidValidationService->validateBid($auction, $bidder, 1000);

        $this->assertFalse($validation['valid']);
        $this->assertStringContainsString('already ended', implode(' ', $validation['errors']));
    }

    /** @test */
    public function it_allows_valid_bids()
    {
        $seller = User::factory()->create(['user_type' => 'seller']);
        $bidder = User::factory()->create(['user_type' => 'customer', 'email_verified_at' => now()]);
        $car = Car::factory()->create(['user_id' => $seller->id]);
        $auction = Auction::factory()->create([
            'seller_id' => $seller->id,
            'car_id' => $car->id,
            'status' => 'active',
            'current_bid' => 1000,
            'bid_increment' => 100,
            'starting_bid' => 500,
            'start_time' => now()->subHour(1), // Started 1 hour ago
            'end_time' => now()->addHours(2)   // Ends in 2 hours
        ]);

        // Minimum bid should be current_bid + bid_increment = 1000 + 100 = 1100
        $validation = $this->bidValidationService->validateBid($auction, $bidder, 1100);

        $this->assertTrue($validation['valid'], 'Validation should pass for valid bid. Errors: ' . implode(', ', $validation['errors']));
        $this->assertEmpty($validation['errors']);
    }
}