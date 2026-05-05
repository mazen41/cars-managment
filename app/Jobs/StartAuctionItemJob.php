<?php

namespace App\Jobs;

use App\Events\AuctionItemStarted;
use App\Models\AuctionItem;
use App\Models\AuctionAuditLog;
use App\Services\AuctionOfferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StartAuctionItemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $auctionItemId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $auctionItemId)
    {
        $this->auctionItemId = $auctionItemId;
        $this->onQueue('auctions');
    }

    /**
     * Execute the job.
     */
    public function handle(AuctionOfferService $offerService): void
    {
        try {
            DB::transaction(function () use ($offerService) {
                $item = AuctionItem::with(['auctionRoom', 'car.brand', 'car.model'])
                    ->lockForUpdate()
                    ->findOrFail($this->auctionItemId);

                // Validate item can be started
                if ($item->status !== 'pending') {
                    Log::warning("Auction item {$item->id} cannot be started. Current status: {$item->status}");
                    return;
                }

                $room = $item->auctionRoom;

                // Calculate ends_at timestamp
                $startsAt = now();
                $endsAt = $startsAt->copy()->addSeconds($room->base_timer_seconds);

                // Update item status to 'active'
                $item->update([
                    'status' => 'active',
                    'started_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'current_price' => $item->starting_price,
                ]);

                // Expire pending offers for the item using AuctionOfferService
                $expiredCount = $offerService->expireOffers($item);

                // Log to audit log
                AuctionAuditLog::create([
                    'auction_room_id' => $room->id,
                    'auction_item_id' => $item->id,
                    'user_id' => null,
                    'action' => 'item_started',
                    'details' => [
                        'car_id' => $item->car_id,
                        'starting_price' => $item->starting_price,
                        'ends_at' => $endsAt->toDateTimeString(),
                        'expired_offers' => $expiredCount,
                    ],
                    'ip_address' => request()->ip(),
                ]);

                // Broadcast AuctionItemStarted event
                broadcast(new AuctionItemStarted($item))->toOthers();

                // Schedule FinalizeAuctionItemJob at ends_at time
                FinalizeAuctionItemJob::dispatch($item->id)
                    ->delay($endsAt)
                    ->onQueue('auctions');

                Log::info("Auction item {$item->id} started successfully. Ends at: {$endsAt}");
            });
        } catch (\Exception $e) {
            Log::error("Failed to start auction item {$this->auctionItemId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("StartAuctionItemJob failed for item {$this->auctionItemId}: " . $exception->getMessage());
    }
}
