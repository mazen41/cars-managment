<?php

namespace App\Jobs;

use App\Events\ItemSold;
use App\Events\ItemUnsold;
use App\Models\AuctionItem;
use App\Models\AuctionAuditLog;
use App\Models\Car;
use App\Services\AuctionInvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinalizeAuctionItemJob implements ShouldQueue
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
    public function handle(AuctionInvoiceService $invoiceService): void
    {
        // Acquire Redis lock to prevent race with late bids
        $lock = Cache::lock("auction-item-{$this->auctionItemId}", 10);

        try {
            if (!$lock->get()) {
                Log::warning("Could not acquire Redis lock for finalizing item {$this->auctionItemId}, retrying");
                $this->release(2);
                return;
            }

            DB::transaction(function () use ($invoiceService) {
                $item = AuctionItem::with(['auctionRoom', 'car.brand', 'car.model', 'currentWinner', 'seller'])
                    ->lockForUpdate()
                    ->findOrFail($this->auctionItemId);

                // Validate item can be finalized
                if ($item->status !== 'active') {
                    Log::warning("Auction item {$item->id} cannot be finalized. Current status: {$item->status}");
                    return;
                }

                $room = $item->auctionRoom;
                $finalizedAt = now();

                // Check if item has bids
                if ($item->total_bids > 0 && $item->current_winner_id) {
                    // Item has bids - mark as sold
                    $item->update([
                        'status' => 'sold',
                        'finalized_at' => $finalizedAt,
                    ]);


                    // Generate buyer invoice
                    $buyerInvoice = $invoiceService->generateBuyerInvoice(
                        $item,
                        $item->currentWinner,
                        (float)$item->current_price
                    );

                    if($item->seller->user_type =='seller') {
                        // Calculate commission
                        $commission = $invoiceService->calculateCommission($room, (float)$item->current_price);

                        // Generate seller payout invoice
                        $sellerInvoice = $invoiceService->generateSellerPayout(
                            $item,
                            $item->seller,
                            (float) $item->current_price,
                            $commission
                        );
                    }

                    // Update car status to 'sold'
                    Car::where('id', $item->car_id)->update(['car_status' => \App\Enums\CarStatusEnum::SOLD]);

                    // Log to audit log
                    AuctionAuditLog::create([
                        'auction_room_id' => $room->id,
                        'auction_item_id' => $item->id,
                        'user_id' => $item->current_winner_id,
                        'action' => 'item_sold',
                        'details' => [
                            'final_price' => $item->current_price,
                            'winner_id' => $item->current_winner_id,
                            'total_bids' => $item->total_bids,
                            'buyer_invoice_id' => $buyerInvoice->id,
                            'seller_invoice_id' => $sellerInvoice->id ?? 'none',
                            'commission' => $commission ?? 0,
                        ],
                        'ip_address' => request()->ip(),
                    ]);

                    // Broadcast ItemSold event
                    broadcast(new ItemSold($item))->toOthers();

                    Log::info("Auction item {$item->id} sold for {$item->current_price} to user {$item->current_winner_id}");
                } else {
                    // No bids - mark as unsold
                    $item->update([
                        'status' => 'unsold',
                        'finalized_at' => $finalizedAt,
                    ]);
                    // mark car as available
                    $item->car()->update(['car_status' => \App\Enums\CarStatusEnum::AVAILABLE]);
                    // Log to audit log
                    AuctionAuditLog::create([
                        'auction_room_id' => $room->id,
                        'auction_item_id' => $item->id,
                        'user_id' => null,
                        'action' => 'item_unsold',
                        'details' => [
                            'starting_price' => $item->starting_price,
                            'reason' => 'no_bids',
                        ],
                        'ip_address' => request()->ip(),
                    ]);

                    // Broadcast ItemUnsold event
                    broadcast(new ItemUnsold($item))->toOthers();

                    Log::info("Auction item {$item->id} ended with no bids");
                }

                // Get next item in sequence
                $nextItem = $room->auctionItems()
                    ->where('status', 'pending')
                    ->where('sequence_order', '>', $item->sequence_order)
                    ->orderBy('sequence_order')
                    ->first();

                if ($nextItem) {
                    // Dispatch StartAuctionItemJob for next item
                    StartAuctionItemJob::dispatch($nextItem->id);
                    Log::info("Starting next auction item {$nextItem->id}");
                } else {
                    // No more items - complete the auction room
                    CompleteAuctionRoomJob::dispatch($room->id);
                    Log::info("No more items in room {$room->id}, completing auction");
                }
            });
        } catch (\Exception $e) {
            Log::error("Failed to finalize auction item {$this->auctionItemId}: " . $e->getMessage());
            throw $e;
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("FinalizeAuctionItemJob failed for item {$this->auctionItemId}: " . $exception->getMessage());
    }
}
