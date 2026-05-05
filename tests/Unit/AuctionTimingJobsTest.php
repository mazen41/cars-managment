<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Auction;
use App\Models\Car;
use App\Models\User;
use App\Jobs\StartAuctionJob;
use App\Jobs\EndAuctionJob;
use App\Jobs\AuctionReminderJob;
use App\Services\AuctionService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;
use Carbon\Carbon;

class AuctionTimingJobsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Event::fake();
    }

    /** @test */
    public function start_auction_job_starts_pending_auction()
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

        // Execute the job
        $job = new StartAuctionJob($auction);
        $job->handle(app(AuctionService::class));

        // Assert auction was started
        $auction->refresh();
        $this->assertEquals(Auction::STATUS_ACTIVE, $auction->status);
    }

    /** @test */
    public function start_auction_job_schedules_end_job_and_reminders()
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

        // Execute the job
        $job = new StartAuctionJob($auction);
        $job->handle(app(AuctionService::class));

        // Assert end job was scheduled
        Queue::assertPushed(EndAuctionJob::class, function ($job) use ($auction) {
            return $job->auction->id === $auction->id;
        });

        // Assert reminder jobs were scheduled
        Queue::assertPushed(AuctionReminderJob::class);
    }

    /** @test */
    public function start_auction_job_does_not_start_non_pending_auction()
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
            'start_time' => now()->subMinute(),
            'end_time' => now()->addHour()
        ]);

        // Execute the job
        $job = new StartAuctionJob($auction);
        $job->handle(app(AuctionService::class));

        // Assert auction status unchanged
        $auction->refresh();
        $this->assertEquals(Auction::STATUS_ACTIVE, $auction->status);
    }

    /** @test */
    public function end_auction_job_ends_active_auction()
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
            'end_time' => now()->subMinute()
        ]);

        // Execute the job
        $job = new EndAuctionJob($auction);
        $job->handle(app(AuctionService::class));

        // Assert auction was ended
        $auction->refresh();
        $this->assertEquals(Auction::STATUS_ENDED, $auction->status);
    }

    /** @test */
    public function end_auction_job_does_not_end_non_active_auction()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_ENDED,
            'start_time' => now()->subHour(),
            'end_time' => now()->subMinute()
        ]);

        // Execute the job
        $job = new EndAuctionJob($auction);
        $job->handle(app(AuctionService::class));

        // Assert auction status unchanged
        $auction->refresh();
        $this->assertEquals(Auction::STATUS_ENDED, $auction->status);
    }

    /** @test */
    public function end_auction_job_reschedules_if_end_time_extended()
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
            'end_time' => now()->addMinutes(30) // Extended end time
        ]);

        // Execute the job
        $job = new EndAuctionJob($auction);
        $job->handle(app(AuctionService::class));

        // Assert auction was not ended
        $auction->refresh();
        $this->assertEquals(Auction::STATUS_ACTIVE, $auction->status);

        // Assert new end job was scheduled
        Queue::assertPushed(EndAuctionJob::class, function ($job) use ($auction) {
            return $job->auction->id === $auction->id;
        });
    }

    /** @test */
    public function auction_reminder_job_sends_notifications()
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
            'end_time' => now()->addMinutes(10)
        ]);

        // Mock notification service
        $notificationService = $this->createMock(NotificationService::class);
        $notificationService->expects($this->once())
            ->method('notifyAuctionEndingIn10Minutes')
            ->with($auction);

        // Execute the job
        $job = new AuctionReminderJob($auction, 10);
        $job->handle($notificationService);
    }

    /** @test */
    public function auction_reminder_job_skips_inactive_auction()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_ENDED,
            'start_time' => now()->subHour(),
            'end_time' => now()->subMinutes(10)
        ]);

        // Mock notification service
        $notificationService = $this->createMock(NotificationService::class);
        $notificationService->expects($this->never())
            ->method('notifyAuctionEndingIn10Minutes');

        // Execute the job
        $job = new AuctionReminderJob($auction, 10);
        $job->handle($notificationService);
    }

    /** @test */
    public function jobs_have_correct_queue_names()
    {
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id
        ]);

        $startJob = new StartAuctionJob($auction);
        $endJob = new EndAuctionJob($auction);
        $reminderJob = new AuctionReminderJob($auction, 10);

        $this->assertEquals('auction-timing', $startJob->queue);
        $this->assertEquals('auction-timing', $endJob->queue);
        $this->assertEquals('auction-notifications', $reminderJob->queue);
    }

    /** @test */
    public function jobs_have_correct_retry_settings()
    {
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $auction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id
        ]);

        $startJob = new StartAuctionJob($auction);
        $endJob = new EndAuctionJob($auction);
        $reminderJob = new AuctionReminderJob($auction, 10);

        $this->assertEquals(3, $startJob->tries);
        $this->assertEquals(3, $endJob->tries);
        $this->assertEquals(2, $reminderJob->tries);

        $this->assertEquals(60, $startJob->timeout);
        $this->assertEquals(120, $endJob->timeout);
        $this->assertEquals(60, $reminderJob->timeout);
    }
}