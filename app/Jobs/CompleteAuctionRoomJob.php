<?php

namespace App\Jobs;

use App\Events\AuctionRoomCompleted;
use App\Models\AuctionRoom;
use App\Models\AuctionAuditLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CompleteAuctionRoomJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $auctionRoomId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $auctionRoomId)
    {
        $this->auctionRoomId = $auctionRoomId;
        $this->onQueue('auctions');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::transaction(function () {
                $room = AuctionRoom::with(['auctionItems'])->lockForUpdate()->findOrFail($this->auctionRoomId);

                // Validate room can be completed
                if ($room->status !== 'active') {
                    Log::warning("Auction room {$room->id} cannot be completed. Current status: {$room->status}");
                    return;
                }

                $completedAt = now();

                // Update room status to 'completed'
                $room->update([
                    'status' => 'completed',
                    'completed_at' => $completedAt,
                ]);

                // Generate room summary statistics
                $statistics = $this->generateStatistics($room);

                // Log to audit log
                AuctionAuditLog::create([
                    'auction_room_id' => $room->id,
                    'auction_item_id' => null,
                    'user_id' => $room->created_by,
                    'action' => 'room_completed',
                    'details' => [
                        'room_name' => $room->name,
                        'started_at' => $room->started_at,
                        'completed_at' => $completedAt,
                        'statistics' => $statistics,
                    ],
                    'ip_address' => request()->ip(),
                ]);

                // Broadcast AuctionRoomCompleted event
                broadcast(new AuctionRoomCompleted($room, $statistics))->toOthers();

                // Notify admin of completion
                $this->notifyAdmin($room, $statistics);

                Log::info("Auction room {$room->id} completed successfully");
            });
        } catch (\Exception $e) {
            Log::error("Failed to complete auction room {$this->auctionRoomId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate room summary statistics.
     */
    protected function generateStatistics(AuctionRoom $room): array
    {
        $items = $room->auctionItems;

        $totalItems = $items->count();
        $soldItems = $items->where('status', 'sold')->count();
        $unsoldItems = $items->where('status', 'unsold')->count();
        $offerAcceptedItems = $items->where('status', 'offer_accepted')->count();

        $totalRevenue = $items->where('status', 'sold')->sum('current_price');
        $totalBids = $items->sum('total_bids');

        $averageSalePrice = $soldItems > 0 ? $totalRevenue / $soldItems : 0;

        $commission = $totalRevenue * ($room->commission_percentage / 100);

        return [
            'total_items' => $totalItems,
            'sold_items' => $soldItems,
            'unsold_items' => $unsoldItems,
            'offer_accepted_items' => $offerAcceptedItems,
            'total_revenue' => $totalRevenue,
            'total_bids' => $totalBids,
            'average_sale_price' => round($averageSalePrice, 2),
            'total_commission' => round($commission, 2),
            'duration_minutes' => $room->started_at->diffInMinutes($room->completed_at),
        ];
    }

    /**
     * Notify admin of auction completion.
     */
    protected function notifyAdmin(AuctionRoom $room, array $statistics): void
    {
        try {

            $admins = \App\Models\User::where('user_type', 'admin')->get();

            // For now, just log the notification
            Log::info("Admin notification: Auction room {$room->id} completed", [
                'room_name' => $room->name,
                'statistics' => $statistics,
            ]);

            // Example: Notification::send($admins, new AuctionCompletedNotification($room, $statistics));
        } catch (\Exception $e) {
            Log::error("Failed to notify admin of auction completion: " . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("CompleteAuctionRoomJob failed for room {$this->auctionRoomId}: " . $exception->getMessage());
    }
}
