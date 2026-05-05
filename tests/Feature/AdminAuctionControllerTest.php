<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Car;
use App\Models\Auction;
use App\Models\AuctionRequest;
use App\Enums\CarModerationStatusEnum;
use App\Enums\CarStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AdminAuctionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $seller;
    protected Car $car;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create seller user
        $this->seller = User::factory()->create([
            'user_type' => 'customer',
            'email_verified_at' => now(),
        ]);

        // Create a car for testing
        $this->car = Car::factory()->create([
            'user_id' => $this->seller->id,
            'moderation_status' => CarModerationStatusEnum::PUBLISHED,
            'car_status' => CarStatusEnum::AVAILABLE,
        ]);
    }

    public function test_admin_can_access_auctions_index()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/auctions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total'
                ],
                'message'
            ]);
    }

    public function test_admin_can_create_auction()
    {
        $auctionData = [
            'title' => 'Test Auction',
            'description' => 'This is a test auction',
            'car_id' => $this->car->id,
            'seller_id' => $this->seller->id,
            'start_time' => now()->addHour()->toISOString(),
            'end_time' => now()->addDays(3)->toISOString(),
            'reserve_price' => 10000.00,
            'starting_bid' => 5000.00,
            'bid_increment' => 100.00,
            'auto_extend_enabled' => true,
            'extend_duration' => 120,
            'max_extensions' => 5,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/auctions', $auctionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'car_id',
                    'seller_id',
                    'admin_id',
                    'status'
                ],
                'message'
            ]);

        $this->assertDatabaseHas('auctions', [
            'title' => 'Test Auction',
            'car_id' => $this->car->id,
            'seller_id' => $this->seller->id,
            'admin_id' => $this->admin->id,
            'status' => Auction::STATUS_PENDING,
        ]);
    }

    public function test_admin_can_view_auction_details()
    {
        $auction = Auction::factory()->create([
            'car_id' => $this->car->id,
            'seller_id' => $this->seller->id,
            'admin_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/admin/auctions/{$auction->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'auction' => [
                        'id',
                        'title',
                        'status',
                        'car',
                        'seller',
                        'admin'
                    ],
                    'stats'
                ],
                'message'
            ]);
    }

    public function test_admin_can_get_auction_statistics()
    {
        // Create some test auctions
        Auction::factory()->count(3)->create([
            'admin_id' => $this->admin->id,
            'status' => Auction::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/auction-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'metrics' => [
                        'total_auctions',
                        'active_auctions',
                        'ended_auctions',
                        'cancelled_auctions',
                        'successful_auctions',
                        'total_bids',
                        'average_bids_per_auction',
                        'success_rate_percentage'
                    ],
                    'active_auctions',
                    'auctions_ending_soon',
                    'pending_requests_count',
                    'summary'
                ],
                'message'
            ]);
    }

    public function test_admin_can_get_available_cars()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/auction-available-cars');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total'
                ],
                'message'
            ]);
    }

    public function test_admin_can_get_sellers()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/auction-sellers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total'
                ],
                'message'
            ]);
    }

    public function test_admin_can_get_auction_requests()
    {
        // Create a test auction request
        AuctionRequest::factory()->create([
            'car_id' => $this->car->id,
            'seller_id' => $this->seller->id,
            'status' => AuctionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/auction-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total'
                ],
                'message'
            ]);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $response = $this->actingAs($this->seller)
            ->getJson('/admin/auctions');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_routes()
    {
        $response = $this->getJson('/admin/auctions');

        $response->assertStatus(401);
    }

    public function test_admin_cannot_create_auction_with_invalid_data()
    {
        $invalidData = [
            'title' => '', // Required field empty
            'car_id' => 999999, // Non-existent car
            'seller_id' => 999999, // Non-existent seller
            'start_time' => now()->subHour()->toISOString(), // Past time
            'end_time' => now()->subHour()->toISOString(), // Before start time
            'reserve_price' => -100, // Negative price
            'starting_bid' => -50, // Negative bid
            'bid_increment' => 0, // Zero increment
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/admin/auctions', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'car_id',
                'seller_id',
                'start_time',
                'end_time',
                'reserve_price',
                'starting_bid',
                'bid_increment'
            ]);
    }
}