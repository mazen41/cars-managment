<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\Car;
use App\Models\NotificationType;
use App\Notifications\AuctionOutbidNotification;
use App\Notifications\AuctionWonNotification;
use App\Notifications\AuctionEndingSoonNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class AuctionNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed notification types
        $this->artisan('db:seed', ['--class' => 'AuctionNotificationTypesSeeder']);
    }

    /** @test */
    public function it_can_create_outbid_notification()
    {
        // Create test data
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'status' => 'active'
        ]);
        
        $bid = AuctionBid::factory()->create([
            'auction_id' => $auction->id,
            'user_id' => $user->id,
            'amount' => 1000
        ]);

        // Create notification
        $notification = new AuctionOutbidNotification($auction, $bid);
        
        // Test notification data
        $data = $notification->toArray($user);
        
        $this->assertArrayHasKey('notification_type_id', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('auction_outbid', $data['data']['type']);
        $this->assertEquals($auction->id, $data['data']['auction_id']);
    }

    /** @test */
    public function it_can_create_auction_won_notification()
    {
        // Create test data
        $winner = User::factory()->create();
        $seller = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'status' => 'ended',
            'winner_id' => $winner->id,
            'final_price' => 5000
        ]);
        
        $winningBid = AuctionBid::factory()->create([
            'auction_id' => $auction->id,
            'user_id' => $winner->id,
            'amount' => 5000,
            'is_winning' => true
        ]);

        // Create notification
        $notification = new AuctionWonNotification($auction, $winningBid);
        
        // Test notification data
        $data = $notification->toArray($winner);
        
        $this->assertArrayHasKey('notification_type_id', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('auction_won', $data['data']['type']);
        $this->assertEquals($auction->id, $data['data']['auction_id']);
        $this->assertEquals(5000, $data['data']['winning_bid']);
    }

    /** @test */
    public function it_can_create_auction_ending_soon_notification()
    {
        // Create test data
        $user = User::factory()->create();
        $seller = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'status' => 'active',
            'end_time' => now()->addMinutes(10)
        ]);

        // Create notification
        $notification = new AuctionEndingSoonNotification($auction, '10 minutes');
        
        // Test notification data
        $data = $notification->toArray($user);
        
        $this->assertArrayHasKey('notification_type_id', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('auction_ending_soon', $data['data']['type']);
        $this->assertEquals($auction->id, $data['data']['auction_id']);
        $this->assertEquals('10 minutes', $data['data']['time_remaining']);
    }

    /** @test */
    public function notification_types_are_properly_seeded()
    {
        $expectedTypes = [
            'auction_outbid',
            'auction_ending_soon', 
            'auction_won',
            'auction_ended_no_winner',
            'auction_time_extended',
            'auction_new_bid',
            'auction_ended_seller',
            'auction_request_approved',
            'auction_request_rejected',
            'auction_new_request',
            'auction_high_activity'
        ];

        foreach ($expectedTypes as $type) {
            $notificationType = NotificationType::where('type', $type)->first();
            $this->assertNotNull($notificationType, "Notification type '{$type}' should exist");
        }
    }
}