<?php

namespace App\Console\Commands;

use App\Models\AuctionOffer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredOffersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:cleanup-expired-offers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired auction offers as expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired auction offers...');

        // Query offers with status 'pending' and expires_at <= now
        $expiredOffers = AuctionOffer::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($expiredOffers->isEmpty()) {
            $this->info('No expired offers found.');
            return;
        }

        $expiredCount = 0;

        foreach ($expiredOffers as $offer) {
            try {
                // Mark as 'expired'
                $offer->update(['status' => 'expired']);
                $expiredCount++;

                Log::info('Auction offer marked as expired', [
                    'offer_id' => $offer->id,
                    'auction_item_id' => $offer->auction_item_id,
                    'buyer_id' => $offer->buyer_id,
                    'amount' => $offer->amount,
                    'expires_at' => $offer->expires_at,
                ]);

            } catch (\Exception $e) {
                $this->error("Failed to expire offer {$offer->id}: {$e->getMessage()}");
                
                Log::error('Failed to mark auction offer as expired', [
                    'offer_id' => $offer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Successfully marked {$expiredCount} offer(s) as expired.");
    }
}