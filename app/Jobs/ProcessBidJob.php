<?php

namespace App\Jobs;

use App\Events\BidAccepted;
use App\Events\BidRejected;
use App\Models\Bid;
use App\Models\AuctionItem;
use App\Models\AuctionAuditLog;
use App\Services\AuctionBiddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBidJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 30;
    public $backoff = [2, 5, 10]; // Exponential backoff in seconds

    protected $bidId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $bidId)
    {
        $this->bidId = $bidId;
        $this->onQueue('auctions');
    }

    /**
     * Execute the job.
     */
    public function handle(AuctionBiddingService $biddingService): void
    {
        $bid = Bid::with(['auctionItem.auctionRoom', 'bidder'])->findOrFail($this->bidId);
        $item = $bid->auctionItem;

        // Acquire Redis distributed lock for the auction item
        $lock = Cache::lock("auction-item-{$item->id}", 10);

        try {
            if (!$lock->get()) {
                // Could not acquire lock, retry job
                Log::warning("Could not acquire Redis lock for item {$item->id}, retrying bid {$this->bidId}");
                $this->release(2); // Retry after 2 seconds
                return;
            }

            // Process bid within database transaction with row locking
            DB::transaction(function () use ($bid, $item, $biddingService) {
                // Acquire database row lock using SELECT FOR UPDATE
                $item = AuctionItem::where('id', $item->id)
                    ->lockForUpdate()
                    ->first();

                // Validate bid amount and user eligibility
                $validation = $biddingService->validateBid($item, $bid->bidder, $bid->amount);

                if (!$validation['valid']) {
                    // Reject the bid
                    $bid->update([
                        'status' => 'rejected',
                        'rejection_reason' => $validation['reason'],
                    ]);

                    // Broadcast BidRejected event
                    broadcast(new BidRejected($bid))->toOthers();

                    // Log to audit log
                    AuctionAuditLog::create([
                        'auction_room_id' => $item->auction_room_id,
                        'auction_item_id' => $item->id,
                        'user_id' => $bid->bidder_id,
                        'action' => 'bid_rejected',
                        'details' => [
                            'bid_id' => $bid->id,
                            'amount' => $bid->amount,
                            'reason' => $validation['reason'],
                        ],
                        'ip_address' => $bid->ip_address,
                    ]);

                    Log::info("Bid {$bid->id} rejected: {$validation['reason']}");
                    return;
                }

                // Calculate timer extension
                $extensionSeconds = $item->auctionRoom->extension_seconds;
                $oldEndsAt = $item->ends_at;
                $newEndsAt = now()->addSeconds($extensionSeconds);

                // Only extend if new time is later than current ends_at
                if ($newEndsAt->greaterThan($oldEndsAt)) {
                    $timeExtendedBy = $extensionSeconds;
                } else {
                    $newEndsAt = $oldEndsAt;
                    $timeExtendedBy = 0;
                }

                // Mark previous bids as 'outbid' (only for the current winner)
                if ($item->current_winner_id) {
                    Bid::where('auction_item_id', $item->id)
                        ->where('bidder_id', $item->current_winner_id)
                        ->where('status', 'accepted')
                        ->update(['status' => 'outbid']);
                }

                // Update item current_price, current_winner_id, ends_at (timer extension)
                $item->update([
                    'current_price' => $bid->amount,
                    'current_winner_id' => $bid->bidder_id,
                    'ends_at' => $newEndsAt,
                    'total_bids' => $item->total_bids + 1,
                    'total_extensions' => $timeExtendedBy > 0 ? $item->total_extensions + 1 : $item->total_extensions,
                ]);

                // Update bid status to 'accepted'
                $bid->update(['status' => 'accepted']);

                // Log to audit log
                AuctionAuditLog::create([
                    'auction_room_id' => $item->auction_room_id,
                    'auction_item_id' => $item->id,
                    'user_id' => $bid->bidder_id,
                    'action' => 'bid_accepted',
                    'details' => [
                        'bid_id' => $bid->id,
                        'amount' => $bid->amount,
                        'previous_price' => $oldEndsAt,
                        'new_price' => $bid->amount,
                        'time_extended_by' => $timeExtendedBy,
                        'new_ends_at' => $newEndsAt->toDateTimeString(),
                    ],
                    'ip_address' => $bid->ip_address,
                ]);

                Log::info("Bid {$bid->id} accepted for item {$item->id}. New price: {$bid->amount}");
            });

            // Broadcast BidAccepted event after database commit
            $item->refresh();
            $timeExtendedBy = $item->auctionRoom->extension_seconds;
            $nextMinimumBid = $biddingService->calculateMinimumBid($item);
            broadcast(new BidAccepted($bid, $item, $timeExtendedBy, $nextMinimumBid))->toOthers();

            // If timer was extended, reschedule FinalizeAuctionItemJob
            if ($item->ends_at->greaterThan(now())) {
                // Cancel existing finalization job and schedule new one
                FinalizeAuctionItemJob::dispatch($item->id)
                    ->delay($item->ends_at)
                    ->onQueue('auctions');
            }

        } catch (\Exception $e) {
            Log::error("Failed to process bid {$this->bidId}: " . $e->getMessage());
            throw $e;
        } finally {
            // Release locks
            optional($lock)->release();
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessBidJob failed for bid {$this->bidId}: " . $exception->getMessage());

        // Mark bid as rejected due to processing failure
        try {
            $bid = Bid::find($this->bidId);
            if ($bid && $bid->status === 'pending') {
                $bid->update([
                    'status' => 'rejected',
                    'rejection_reason' => 'Processing failed after multiple attempts',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to mark bid as rejected: " . $e->getMessage());
        }
    }
}
