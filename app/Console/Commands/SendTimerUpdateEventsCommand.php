<?php

namespace App\Console\Commands;

use App\Events\TimerUpdate;
use App\Models\AuctionItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTimerUpdateEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:send-timer-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send timer update events for active auction items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Query active auction items
        $activeItems = AuctionItem::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now())
            ->get();

        if ($activeItems->isEmpty()) {
            $this->info('No active auction items found.');
            return;
        }

        $eventCount = 0;

        foreach ($activeItems as $item) {
            try {
                $secondsRemaining = max(0, now()->diffInSeconds($item->ends_at, false));

                // Only broadcast if there's time remaining
                if ($secondsRemaining > 0) {
                    // Broadcast TimerUpdate event for each item
                    broadcast(new TimerUpdate($item));
                    $eventCount++;
                }

            } catch (\Exception $e) {
                $this->error("Failed to send timer update for item {$item->id}: {$e->getMessage()}");

                Log::error('Failed to send timer update event', [
                    'item_id' => $item->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent timer update events for {$eventCount} active auction item(s).");
    }
}
