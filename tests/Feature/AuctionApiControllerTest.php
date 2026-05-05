<?php

namespace Tests\Feature;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionRequest;
use App\Models\Car;
use App\Models\User;
use App\Services\AuctionService;
use App\Services\BiddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuctionApiControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $seller;
    protected $car;
    protected $auction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->seller = User::factory()->create();
        $this->car = Car::factory()->create(['user_id' => $this->seller->id]);
        $this->auction = Auction::factory()->create([
            'car_id' => $this->car->id,
            'seller_id' => $this->seller->id,
            'status' => Auction::STATUS_ACTIVE,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
        ]);
    }

    public function test_can_get_auction_list()
    {
        $response = $this->getJson('/api/v2/auctions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'status',
                            'current_bid',
                            'end_time',
                            'car',
                            'seller'
                        ]
                    ]
                ],
                'message'
            ]);
    }

    public function test_can_get_auction_details()
    {
        $response = $this->getJson("/api/v2/auctions/{$this->auction->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'auction' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'current_bid',
                        'car',
                        'seller'
                    ],
                    'bid_stats' => [
                        'total_bids',
                        'unique_bidders',
                        'highest_bid',
                        'time_remaining',
                        'is_active'
                    ]
                ],
                'message'
            ]);
    }

    public function test_authenticated_user_can_place_bid()
    {
        Sanctum::actingAs($this->user);

        $bidAmount = $this->auction->getMinimumBidAmount();

        // Mock the bidding service
        $this->mock(BiddingService::class, function ($mock) use ($bidAmount) {
            $mock->shouldReceive('placeBid')
                ->once()
                ->andReturn(AuctionBid::factory()->make([
                    'auction_id' => $this->auction->id,
                    'user_id' => $this->user->id,
                    'amount' => $bidAmount
                ]));
        });

        $response = $this->postJson("/api/v2/auctions/{$this->auction->id}/bid", [
            'amount' => $bidAmount
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'bid',
                    'auction'
                ],
                'message'
            ]);
    }

    public function test_unauthenticated_user_cannot_place_bid()
    {
        $response = $this->postJson("/api/v2/auctions/{$this->auction->id}/bid", [
            'amount' => 1000
        ]);

        $response->assertStatus(401);
    }

    public function test_can_get_bid_history()
    {
        // Create some bids
        AuctionBid::factory()->count(3)->create([
            'auction_id' => $this->auction->id,
            'is_valid' => true
        ]);

        $response = $this->getJson("/api/v2/auctions/{$this->auction->id}/bids");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'amount',
                            'bid_time',
                            'user'
                        ]
                    ]
                ],
                'message'
            ]);
    }

    public function test_authenticated_user_can_request_auction()
    {
        Sanctum::actingAs($this->seller);

        $newCar = Car::factory()->create([
            'user_id' => $this->seller->id,
            'published' => 1,
            'approved' => 1
        ]);

        $response = $this->postJson('/api/v2/auction-requests', [
            'car_id' => $newCar->id,
            'requested_reserve_price' => 5000,
            'preferred_duration' => 48,
            'notes' => 'Please consider this car for auction'
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'car_id',
                    'seller_id',
                    'requested_reserve_price',
                    'preferred_duration',
                    'status'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('auction_requests', [
            'car_id' => $newCar->id,
            'seller_id' => $this->seller->id,
            'status' => AuctionRequest::STATUS_PENDING
        ]);
    }

    public function test_user_cannot_request_auction_for_others_car()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v2/auction-requests', [
            'car_id' => $this->car->id, // This car belongs to $this->seller, not $this->user
            'requested_reserve_price' => 5000,
            'preferred_duration' => 48
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['car_id']);
    }

    public function test_can_get_user_bid_history()
    {
        Sanctum::actingAs($this->user);

        // Create some bids for the user
        AuctionBid::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_valid' => true
        ]);

        $response = $this->getJson('/api/v2/my-bids');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'amount',
                            'bid_time',
                            'auction'
                        ]
                    ]
                ],
                'message'
            ]);
    }

    public function test_can_get_user_auctions()
    {
        Sanctum::actingAs($this->seller);

        $response = $this->getJson('/api/v2/my-auctions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'status',
                            'car'
                        ]
                    ]
                ],
                'message'
            ]);
    }

    public function test_can_get_featured_auctions()
    {
        // Create some auctions with different bid amounts
        Auction::factory()->count(3)->create([
            'status' => Auction::STATUS_ACTIVE,
            'end_time' => now()->addHours(2)
        ]);

        $response = $this->getJson('/api/v2/auctions/featured');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'current_bid',
                        'car',
                        'seller'
                    ]
                ],
                'message'
            ]);
    }

    public function test_can_get_ending_soon_auctions()
    {
        // Create auction ending soon
        Auction::factory()->create([
            'status' => Auction::STATUS_ACTIVE,
            'end_time' => now()->addMinutes(30)
        ]);

        $response = $this->getJson('/api/v2/auctions/ending-soon');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'end_time',
                        'car',
                        'seller'
                    ]
                ],
                'message'
            ]);
    }

    public function test_bid_validation_prevents_invalid_bids()
    {
        Sanctum::actingAs($this->user);

        // Test bid too low
        $response = $this->postJson("/api/v2/auctions/{$this->auction->id}/bid", [
            'amount' => 1 // Too low
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);

        // Test seller cannot bid on own auction
        Sanctum::actingAs($this->seller);

        $response = $this->postJson("/api/v2/auctions/{$this->auction->id}/bid", [
            'amount' => $this->auction->getMinimumBidAmount()
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_can_get_user_auction_requests()
    {
        Sanctum::actingAs($this->seller);

        // Create some auction requests
        AuctionRequest::factory()->count(2)->create([
            'seller_id' => $this->seller->id
        ]);

        $response = $this->getJson('/api/v2/my-auction-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'car_id',
                            'seller_id',
                            'status',
                            'car'
                        ]
                    ]
                ],
                'message'
            ]);
    }
}