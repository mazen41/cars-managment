<?php

namespace App\Console\Commands;

use App\Jobs\StartAuctionRoomJob;
use App\Models\AuctionRoom;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StartScheduledAuctionRoomsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:start-scheduled-rooms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start auction rooms that are scheduled to begin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for scheduled auction rooms to start...');

        // Query auction rooms with status 'scheduled' and scheduled_start_at <= now
        $scheduledRooms = AuctionRoom::where('status', 'scheduled')
            ->where('scheduled_start_at', '<=', now())
            ->get();

        if ($scheduledRooms->isEmpty()) {
            $this->info('No auction rooms scheduled to start at this time.');
            return;
        }

        $startedCount = 0;

        foreach ($scheduledRooms as $room) {
            try {

                if(!$room->canStart()){
                    \Log::info("Auction room {$room->id} cannot be started due to insufficient pending items.");
                    continue;
                }
                // Dispatch StartAuctionRoomJob for each room
                StartAuctionRoomJob::dispatch($room->id);

                $this->info("Dispatched start job for auction room: {$room->name} (ID: {$room->id})");
                $startedCount++;

                Log::info('Scheduled auction room start job dispatched', [
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'scheduled_start_at' => $room->scheduled_start_at,
                ]);

            } catch (\Exception $e) {
                $this->error("Failed to start auction room {$room->name} (ID: {$room->id}): {$e->getMessage()}");

                Log::error('Failed to dispatch auction room start job', [
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Successfully dispatched start jobs for {$startedCount} auction room(s).");
    }
}
