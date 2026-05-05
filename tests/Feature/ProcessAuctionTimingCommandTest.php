<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Auction;
use App\Models\Car;
use App\Models\User;
use App\Jobs\StartAuctionJob;
use App\Jobs\EndAuctionJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;

class ProcessAuctionTimingCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /** @test */
    public function command_schedules_jobs_for_pending_auctions()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $pendingAuction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_PENDING,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2)
        ]);

        // Run command with schedule option
        Artisan::call('auction:process-timing', ['--schedule' => true]);

        // Assert start job was scheduled
        Queue::assertPushed(StartAuctionJob::class, function ($job) use ($pendingAuction) {
            return $job->auction->id === $pendingAuction->id;
        });
    }

    /** @test */
    public function command_schedules_jobs_for_active_auctions()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $activeAuction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_ACTIVE,
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour()
        ]);

        // Run command with schedule option
        Artisan::call('auction:process-timing', ['--schedule' => true]);

        // Assert end job was scheduled
        Queue::assertPushed(EndAuctionJob::class, function ($job) use ($activeAuction) {
            return $job->auction->id === $activeAuction->id;
        });
    }

    /** @test */
    public function command_processes_immediate_auctions()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car1 = Car::factory()->create(['user_id' => $seller->id]);
        $car2 = Car::factory()->create(['user_id' => $seller->id]);
        
        // Auction that should start now
        $auctionToStart = Auction::factory()->create([
            'car_id' => $car1->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_PENDING,
            'start_time' => now()->subMinute(),
            'end_time' => now()->addHour()
        ]);

        // Auction that should end now
        $auctionToEnd = Auction::factory()->create([
            'car_id' => $car2->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_ACTIVE,
            'start_time' => now()->subHour(),
            'end_time' => now()->subMinute()
        ]);

        // Run command with immediate option
        Artisan::call('auction:process-timing', ['--immediate' => true]);

        // Assert auctions were processed
        $auctionToStart->refresh();
        $auctionToEnd->refresh();

        $this->assertEquals(Auction::STATUS_ACTIVE, $auctionToStart->status);
        $this->assertEquals(Auction::STATUS_ENDED, $auctionToEnd->status);
    }

    /** @test */
    public function command_runs_both_schedule_and_immediate_by_default()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car1 = Car::factory()->create(['user_id' => $seller->id]);
        $car2 = Car::factory()->create(['user_id' => $seller->id]);
        $car3 = Car::factory()->create(['user_id' => $seller->id]);
        
        // Future auction to schedule
        $futureAuction = Auction::factory()->create([
            'car_id' => $car1->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_PENDING,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2)
        ]);

        // Auction that should start now
        $auctionToStart = Auction::factory()->create([
            'car_id' => $car2->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_PENDING,
            'start_time' => now()->subMinute(),
            'end_time' => now()->addHour()
        ]);

        // Run command without options (should do both)
        Artisan::call('auction:process-timing');

        // Assert future auction job was scheduled
        Queue::assertPushed(StartAuctionJob::class, function ($job) use ($futureAuction) {
            return $job->auction->id === $futureAuction->id;
        });

        // Assert immediate auction was started
        $auctionToStart->refresh();
        $this->assertEquals(Auction::STATUS_ACTIVE, $auctionToStart->status);
    }

    /** @test */
    public function command_displays_results()
    {
        // Create test data
        $seller = User::factory()->create();
        $admin = User::factory()->create();
        $car = Car::factory()->create(['user_id' => $seller->id]);
        
        $pendingAuction = Auction::factory()->create([
            'car_id' => $car->id,
            'seller_id' => $seller->id,
            'admin_id' => $admin->id,
            'status' => Auction::STATUS_PENDING,
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(2)
        ]);

        // Run command
        Artisan::call('auction:process-timing');
        $output = Artisan::output();

        // Assert output contains results
        $this->assertStringContainsString('Processing auction timing', $output);
        $this->assertStringContainsString('Auction timing processing completed', $output);
        $this->assertStringContainsString('Scheduled 1 start jobs', $output);
    }

    /** @test */
    public function command_handles_no_auctions_gracefully()
    {
        // Run command with no auctions
        Artisan::call('auction:process-timing');
        $output = Artisan::output();

        // Assert appropriate message is shown
        $this->assertStringContainsString('No auctions required processing', $output);
    }

    /** @test */
    public function command_returns_success_exit_code()
    {
        $exitCode = Artisan::call('auction:process-timing');
        $this->assertEquals(0, $exitCode);
    }
}