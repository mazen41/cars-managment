<?php

namespace Tests\Feature;

use App\Events\AuctionEnded;
use App\Events\AuctionStatusChanged;
use App\Events\AuctionTimeExtended;
use App\Events\BidPlaced;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\Car;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuctionWebSocketEventsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->seller = User::factory()->create(['user_type' => 'customer']);
        $this->admin = User::factory()->create(['user_type' => 'admin']);
        $this->bidder = User::factory()->create(['user_type' => 'customer']);
        
        // Create test car
        $this->car = Car::factory()->create(['user_id' => $this->seller->id]);
        
        // Create test auction
        $this->auction = Auction::factory()->create([
            'car_id' => $this->car->id,
            'seller_id' => $this->seller->id,
            'admin_id' => $this->admin->id,
            'status' => Auction::STATUS_ACTIVE,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'starting_bid' => 1000,
            'bid_increment' => 100,
            'current_bid' => 0,
        ]);
    }

    /** @test */
    public function bid_placed_event_broadcasts_correctly()
    {
        Event::fake();

        // Create a bid
        $bid = AuctionBid::create([
            'auction_id' => $this->auction->id,
            'user_id' => $this->bidder->id,
            'amount' => 1100,
            'bid_time' => now(),
            'is_winning' => true,
            'is_valid' => true,
        ]);

        // Fire the event
        $event = new BidPlaced($this->auction, $bid);
        event($event);

        // Assert event was dispatched
        Event::assertDispatched(BidPlaced::class, function ($event) use ($bid) {
            return $event->auction->id === $this->auction->id &&
                   $event->bid->id === $bid->id &&
                   $event->bidder->id === $this->bidder->id;
        });

        // Test broadcast data
        $broadcastData = $event->broadcastWith();
        
        $this->assertArrayHasKey('auction_id', $broadcastData);
        $this->assertArrayHasKey('bid', $broadcastData);
        $this->assertArrayHasKey('bidder', $broadcastData);
        $this->assertArrayHasKey('auction', $broadcastData);
        $this->assertArrayHasKey('timestamp', $broadcastData);
        
        $this->assertEquals($this->auction->id, $broadcastData['auction_id']);
        $this->assertEquals($bid->id, $broadcastData['bid']['id']);
        $this->assertEquals($this->bidder->id, $broadcastData['bidder']['id']);
    }

    /** @test */
    public function auction_status_changed_event_broadcasts_correctly()
    {
        Event::fake();

        $previousStatus = Auction::STATUS_PENDING;
        $newStatus = Auction::STATUS_ACTIVE;

        // Fire the event
        $event = new AuctionStatusChanged($this->auction, $previousStatus, $newStatus);
        event($event);

        // Assert event was dispatched
        Event::assertDispatched(AuctionStatusChanged::class, function ($event) use ($previousStatus, $newStatus) {
            return $event->auction->id === $this->auction->id &&
                   $event->previousStatus === $previousStatus &&
                   $event->newStatus === $newStatus;
        });

        // Test broadcast data
        $broadcastData = $event->broadcastWith();
        
        $this->assertArrayHasKey('auction_id', $broadcastData);
        $this->assertArrayHasKey('previous_status', $broadcastData);
        $this->assertArrayHasKey('new_status', $broadcastData);
        $this->assertArrayHasKey('auction', $broadcastData);
        $this->assertArrayHasKey('timestamp', $broadcastData);
        
        $this->assertEquals($this->auction->id, $broadcastData['auction_id']);
        $this->assertEquals($previousStatus, $broadcastData['previous_status']);
        $this->assertEquals($newStatus, $broadcastData['new_status']);
    }

    /** @test */
    public function auction_time_extended_event_broadcasts_correctly()
    {
        Event::fake();

        $extensionSeconds = 120;

        // Fire the event
        $event = new AuctionTimeExtended($this->auction, $extensionSeconds);
        event($event);

        // Assert event was dispatched
        Event::assertDispatched(AuctionTimeExtended::class, function ($event) use ($extensionSeconds) {
            return $event->auction->id === $this->auction->id &&
                   $event->extensionSeconds === $extensionSeconds;
        });

        // Test broadcast data
        $broadcastData = $event->broadcastWith();
        
        $this->assertArrayHasKey('auction_id', $broadcastData);
        $this->assertArrayHasKey('extension_seconds', $broadcastData);
        $this->assertArrayHasKey('extension_minutes', $broadcastData);
        $this->assertArrayHasKey('new_end_time', $broadcastData);
        $this->assertArrayHasKey('time_remaining_seconds', $broadcastData);
        $this->assertArrayHasKey('extensions_count', $broadcastData);
        $this->assertArrayHasKey('auction', $broadcastData);
        $this->assertArrayHasKey('timestamp', $broadcastData);
        
        $this->assertEquals($this->auction->id, $broadcastData['auction_id']);
        $this->assertEquals($extensionSeconds, $broadcastData['extension_seconds']);
        $this->assertEquals(2.0, $broadcastData['extension_minutes']);
    }

    /** @test */
    public function auction_ended_event_broadcasts_correctly_with_winner()
    {
        Event::fake();

        // Create winning bid
        $winningBid = AuctionBid::create([
            'auction_id' => $this->auction->id,
            'user_id' => $this->bidder->id,
            'amount' => 1500,
            'bid_time' => now(),
            'is_winning' => true,
            'is_valid' => true,
        ]);

        // Update auction with winner
        $this->auction->update([
            'status' => Auction::STATUS_ENDED,
            'winner_id' => $this->bidder->id,
            'final_price' => 1500,
            'current_bid' => 1500,
        ]);

        // Fire the event
        $event = new AuctionEnded($this->auction, $winningBid);
        event($event);

        // Assert event was dispatched
        Event::assertDispatched(AuctionEnded::class, function ($event) use ($winningBid) {
            return $event->auction->id === $this->auction->id &&
                   $event->winningBid->id === $winningBid->id &&
                   $event->winner->id === $this->bidder->id;
        });

        // Test broadcast data
        $broadcastData = $event->broadcastWith();
        
        $this->assertArrayHasKey('auction_id', $broadcastData);
        $this->assertArrayHasKey('has_winner', $broadcastData);
        $this->assertArrayHasKey('winning_bid', $broadcastData);
        $this->assertArrayHasKey('winner', $broadcastData);
        $this->assertArrayHasKey('final_price', $broadcastData);
        $this->assertArrayHasKey('auction', $broadcastData);
        $this->assertArrayHasKey('timestamp', $broadcastData);
        
        $this->assertEquals($this->auction->id, $broadcastData['auction_id']);
        $this->assertTrue($broadcastData['has_winner']);
        $this->assertEquals($winningBid->id, $broadcastData['winning_bid']['id']);
        $this->assertEquals($this->bidder->id, $broadcastData['winner']['id']);
    }

    /** @test */
    public function auction_ended_event_broadcasts_correctly_without_winner()
    {
        Event::fake();

        // Update auction as ended without winner
        $this->auction->update([
            'status' => Auction::STATUS_ENDED,
            'final_price' => 0,
        ]);

        // Fire the event
        $event = new AuctionEnded($this->auction);
        event($event);

        // Assert event was dispatched
        Event::assertDispatched(AuctionEnded::class, function ($event) {
            return $event->auction->id === $this->auction->id &&
                   $event->winningBid === null &&
                   $event->winner === null;
        });

        // Test broadcast data
        $broadcastData = $event->broadcastWith();
        
        $this->assertArrayHasKey('auction_id', $broadcastData);
        $this->assertArrayHasKey('has_winner', $broadcastData);
        $this->assertArrayNotHasKey('winning_bid', $broadcastData);
        $this->assertArrayNotHasKey('winner', $broadcastData);
        $this->assertArrayHasKey('final_price', $broadcastData);
        $this->assertArrayHasKey('auction', $broadcastData);
        $this->assertArrayHasKey('timestamp', $broadcastData);
        
        $this->assertEquals($this->auction->id, $broadcastData['auction_id']);
        $this->assertFalse($broadcastData['has_winner']);
    }

    /** @test */
    public function events_broadcast_on_correct_channels()
    {
        // Create a bid
        $bid = AuctionBid::create([
            'auction_id' => $this->auction->id,
            'user_id' => $this->bidder->id,
            'amount' => 1100,
            'bid_time' => now(),
            'is_winning' => true,
            'is_valid' => true,
        ]);

        // Test BidPlaced channels
        $bidEvent = new BidPlaced($this->auction, $bid);
        $channels = $bidEvent->broadcastOn();
        
        $this->assertCount(2, $channels);
        $this->assertEquals("auction.{$this->auction->id}", $channels[0]->name);
        $this->assertEquals("auction.global", $channels[1]->name);

        // Test AuctionStatusChanged channels
        $statusEvent = new AuctionStatusChanged($this->auction, 'pending', 'active');
        $channels = $statusEvent->broadcastOn();
        
        $this->assertCount(2, $channels);
        $this->assertEquals("auction.{$this->auction->id}", $channels[0]->name);
        $this->assertEquals("auction.global", $channels[1]->name);

        // Test AuctionTimeExtended channels
        $extendEvent = new AuctionTimeExtended($this->auction, 120);
        $channels = $extendEvent->broadcastOn();
        
        $this->assertCount(1, $channels);
        $this->assertEquals("auction.{$this->auction->id}", $channels[0]->name);

        // Test AuctionEnded channels
        $endEvent = new AuctionEnded($this->auction, $bid);
        $channels = $endEvent->broadcastOn();
        
        $this->assertCount(2, $channels);
        $this->assertEquals("auction.{$this->auction->id}", $channels[0]->name);
        $this->assertEquals("auction.global", $channels[1]->name);
    }

    /** @test */
    public function events_have_correct_broadcast_names()
    {
        $bid = AuctionBid::create([
            'auction_id' => $this->auction->id,
            'user_id' => $this->bidder->id,
            'amount' => 1100,
            'bid_time' => now(),
            'is_winning' => true,
            'is_valid' => true,
        ]);

        $bidEvent = new BidPlaced($this->auction, $bid);
        $this->assertEquals('bid.placed', $bidEvent->broadcastAs());

        $statusEvent = new AuctionStatusChanged($this->auction, 'pending', 'active');
        $this->assertEquals('auction.status.changed', $statusEvent->broadcastAs());

        $extendEvent = new AuctionTimeExtended($this->auction, 120);
        $this->assertEquals('auction.time.extended', $extendEvent->broadcastAs());

        $endEvent = new AuctionEnded($this->auction, $bid);
        $this->assertEquals('auction.ended', $endEvent->broadcastAs());
    }
}