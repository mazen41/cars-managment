<?php

namespace App\Jobs;

use App\Events\AuctionRoomStarted;
use App\Models\AuctionRoom;
use App\Models\AuctionAuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StartAuctionRoomJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $auctionRoomId;

    protected $manual;

    /**
     * Create a new job instance.
     */
    public function __construct(int $auctionRoomId, bool $manual = false)
    {
        $this->auctionRoomId = $auctionRoomId;
        $this->manual = $manual;
        $this->onQueue('auctions');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::transaction(function () {
                $room = AuctionRoom::lockForUpdate()->findOrFail($this->auctionRoomId);

                // Validate room is ready to start
                if ($room->status !== 'scheduled') {
                    if ($this->manual) {
                        Log::info("Manual start attempted for auction room {$room->id}. Current status: {$room->status}");
                    } else {
                        Log::warning("Auction room {$room->id} cannot be started automatically. Current status: {$room->status}");
                        return;
                    }
                }

                // Get the first item in sequence
                $firstItem = $room->auctionItems()
                    ->where('status', 'pending')
                    ->orderBy('sequence_order')
                    ->first();

                if (!$firstItem) {
                    Log::error("Auction room {$room->id} has no items to auction");
                    return;
                }

                // Update room status to 'active' and set started_at
                $room->update([
                    'status' => 'active',
                    'started_at' => now(),
                ]);

                // Log to audit log
                AuctionAuditLog::create([
                    'auction_room_id' => $room->id,
                    'auction_item_id' => null,
                    'user_id' => $room->created_by,
                    'action' => 'room_started',
                    'details' => [
                        'room_name' => $room->name,
                        'first_item_id' => $firstItem->id,
                        'total_items' => $room->auctionItems()->count(),
                    ],
                    'ip_address' => request()->ip(),
                ]);

                // Broadcast AuctionRoomStarted event
                broadcast(new AuctionRoomStarted($room, $firstItem->id))->toOthers();

                // Dispatch StartAuctionItemJob for first item
                StartAuctionItemJob::dispatch($firstItem->id);

                Log::info("Auction room {$room->id} started successfully with first item {$firstItem->id}");
            });
        } catch (\Exception $e) {
            Log::error("Failed to start auction room {$this->auctionRoomId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("StartAuctionRoomJob failed for room {$this->auctionRoomId}: " . $exception->getMessage());

        // Optionally notify admin of failure
    }
}
