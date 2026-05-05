<?php

namespace App\Jobs;

use App\Events\OfferAccepted;
use App\Models\AuctionOffer;
use App\Models\AuctionItem;
use App\Models\AuctionAuditLog;
use App\Models\Car;
use App\Services\AuctionInvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessOfferAcceptanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [10, 30, 60];

    protected $auctionOfferId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $auctionOfferId)
    {
        $this->auctionOfferId = $auctionOfferId;
        $this->onQueue('auctions');
    }

    /**
     * Execute the job.
     */
    public function handle(AuctionInvoiceService $invoiceService): void
    {
        try {
            DB::transaction(function () use ($invoiceService) {
                $offer = AuctionOffer::with(['auctionItem.auctionRoom', 'buyer', 'seller'])
                    ->lockForUpdate()
                    ->findOrFail($this->auctionOfferId);

                // Validate offer can be processed
                if ($offer->status !== 'accepted') {
                    Log::warning("Offer {$offer->id} cannot be processed. Current status: {$offer->status}");
                    return;
                }

                $item = $offer->auctionItem;
                $room = $item->auctionRoom;

                // Remove item from auction room (update status)
                $item->update([
                    'status' => 'offer_accepted',
                    'finalized_at' => now(),
                    'current_price' => $offer->amount,
                    'current_winner_id' => $offer->buyer_id,
                ]);

                // Generate buyer invoice using AuctionInvoiceService
                $buyerInvoice = $invoiceService->generateBuyerInvoice(
                    $item,
                    $offer->buyer,
                    $offer->amount
                );

                // Calculate commission
                $commission = $invoiceService->calculateCommission($room, floatval($offer->amount));

                // Generate seller payout invoice using AuctionInvoiceService
                $sellerInvoice = $invoiceService->generateSellerPayout(
                    $item,
                    $offer->seller,
                    $offer->amount,
                    $commission
                );

                // Update car status to 'sold'
                Car::where('id', $item->car_id)->update(['car_status' => \App\Enums\CarStatusEnum::SOLD]);

                // Expire other pending offers for this item
                AuctionOffer::where('auction_item_id', $item->id)
                    ->where('id', '!=', $offer->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'expired',
                        'responded_at' => now(),
                    ]);

                // Log to audit log
                AuctionAuditLog::create([
                    'auction_room_id' => $room->id,
                    'auction_item_id' => $item->id,
                    'user_id' => $offer->seller_id,
                    'action' => 'offer_accepted',
                    'details' => [
                        'offer_id' => $offer->id,
                        'buyer_id' => $offer->buyer_id,
                        'amount' => $offer->amount,
                        'buyer_invoice_id' => $buyerInvoice->id,
                        'seller_invoice_id' => $sellerInvoice->id,
                        'commission' => $commission,
                    ],
                    'ip_address' => request()->ip(),
                ]);

                // Broadcast OfferAccepted event
                broadcast(new OfferAccepted($offer))->toOthers();

                // Notify buyer and seller
                $this->notifyBuyerAndSeller($offer, $buyerInvoice, $sellerInvoice);

                Log::info("Offer {$offer->id} processed successfully for item {$item->id}");
            });
        } catch (\Exception $e) {
            Log::error("Failed to process offer acceptance {$this->auctionOfferId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Notify buyer and seller of offer acceptance.
     */
    protected function notifyBuyerAndSeller($offer, $buyerInvoice, $sellerInvoice): void
    {
        try {
            // Notify buyer
            Log::info("Buyer notification: Offer {$offer->id} accepted", [
                'buyer_id' => $offer->buyer_id,
                'amount' => $offer->amount,
                'invoice_id' => $buyerInvoice->id,
            ]);

            // Notify seller
            Log::info("Seller notification: Offer {$offer->id} accepted", [
                'seller_id' => $offer->seller_id,
                'amount' => $offer->amount,
                'payout_id' => $sellerInvoice->id,
            ]);

            // TODO: implement custom notification classes here
            // Notification::send($offer->buyer, new OfferAcceptedBuyerNotification($offer, $buyerInvoice));
            // Notification::send($offer->seller, new OfferAcceptedSellerNotification($offer, $sellerInvoice));
        } catch (\Exception $e) {
            Log::error("Failed to notify buyer/seller of offer acceptance: " . $e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessOfferAcceptanceJob failed for offer {$this->auctionOfferId}: " . $exception->getMessage());
    }
}
