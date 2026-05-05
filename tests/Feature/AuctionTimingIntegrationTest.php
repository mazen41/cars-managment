<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Auction;
use App\Models\Car;
use App\Models\User;
use App\Jobs\StartAuctionJob;
use App\Jobs\EndAuctionJob;
use App\Jobs\AuctionReminderJob;
use App\Services\AuctionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

class AuctionTimingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Event::fake();
    }

    /** @test */
    public function auction_creation_schedules_start_job_for_future_auction()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auctionService = app(AuctionService::class);
        
        // Create auction in the future
        $auction = $auctionService->createAuction([
            'title' => 'Test Auction',
            'description' => 'Test Description',
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2),
            'reserve_price' => 10000,
            'starting_bid' => 5000,
            'bid_increment' => 100
        ]);

        // Assert start job was scheduled
        Queue::assertPushed(StartAuctionJob::class, function ($job) use ($auction) {
            return $job->auction->id === $auction->id;
        });

        // Assert auction is pending
        $this->assertEquals(Auction::STATUS_PENDING, $auction->status);
    }

    /** @test */
    public function starting_auction_schedules_end_job_and_reminders()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_PENDING,
            'start_time' => now()->subMinute(),
            'end_time' => now()->addHour()
        ]);

        $auctionService = app(AuctionService::class);
        
        // Start the auction
        $auctionService->startAuction($auction);

        // Assert auction is now active
        $auction->refresh();
        $this->assertEquals(Auction::STATUS_ACTIVE, $auction->status);

        // Assert end job was scheduled
        Queue::assertPushed(EndAuctionJob::class, function ($job) use ($auction) {
            return $job->auction->id === $auction->id;
        });
    }

    /** @test */
    public function extending_auction_reschedules_end_job()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_ACTIVE,
            'start_time' => now()->subHour(),
            'end_time' => now()->addMinutes(30),
            'auto_extend_enabled' => true,
            'max_extensions' => 5,
            'extensions_count' => 0
        ]);

        $auctionService = app(AuctionService::class);
        
        // Extend the auction
        $auctionService->extendAuction($auction, 120); // 2 minutes

        // Assert auction was extended
        $auction->refresh();
        $this->assertEquals(1, $auction->extensions_count);

        // Assert new end job was scheduled
        Queue::assertPushed(EndAuctionJob::class, function ($job) use ($auction) {
            return $job->auction->id === $auction->id;
        });
    }

    /** @test */
    public function complete_auction_workflow_with_jobs()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auctionService = app(AuctionService::class);
        
        // Step 1: Create auction
        $auction = $auctionService->createAuction([
            'title' => 'Complete Workflow Test',
            'description' => 'Testing complete workflow',
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'start_time' => now()->addMinutes(5),
            'end_time' => now()->addMinutes(65),
            'reserve_price' => 10000,
            'starting_bid' => 5000,
            'bid_increment' => 100
        ]);

        // Assert start job scheduled
        Queue::assertPushed(StartAuctionJob::class);
        
        // Step 2: Simulate start job execution
        $startJob = new StartAuctionJob($auction);
        
        // Update auction to simulate time passing
        $auction->update(['start_time' => now()->subMinute()]);
        
        $startJob->handle($auctionService);

        // Assert auction started and end job scheduled
        $auction->refresh();
        $this->assertEquals(Auction::STATUS_ACTIVE, $auction->status);
        Queue::assertPushed(EndAuctionJob::class);
        Queue::assertPushed(AuctionReminderJob::class);

        // Step 3: Simulate end job execution
        $endJob = new EndAuctionJob($auction);
        
        // Update auction to simulate time passing
        $auction->update(['end_time' => now()->subMinute()]);
        
        $endJob->handle($auctionService);

        // Assert auction ended
        $auction->refresh();
        $this->assertEquals(Auction::STATUS_ENDED, $auction->status);
    }

    /** @test */
    public function reminder_jobs_are_scheduled_correctly()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_PENDING,
            'start_time' => now()->subMinute(),
            'end_time' => now()->addMinutes(15) // 15 minutes from now
        ]);

        // Execute start job
        $startJob = new StartAuctionJob($auction);
        $startJob->handle(app(AuctionService::class));

        // Assert reminder jobs were scheduled
        Queue::assertPushed(AuctionReminderJob::class, function ($job) {
            return $job->minutesRemaining == 10;
        });

        Queue::assertPushed(AuctionReminderJob::class, function ($job) {
            return $job->minutesRemaining == 2;
        });

        Queue::assertPushed(AuctionReminderJob::class, function ($job) {
            return $job->minutesRemaining == 0.5;
        });
    }
}