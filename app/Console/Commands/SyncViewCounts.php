<?php

namespace App\Console\Commands;

use App\Services\ViewTrackingService;
use Illuminate\Console\Command;

class SyncViewCounts extends Command
{
    protected $signature = 'views:sync
                            {service : Fully-qualified service class (e.g. App\\Services\\CarViewTrackingService)}
                            {--id= : Sync a specific model ID only}';

    protected $description = 'Sync view counts from Redis to database for any ViewTrackingService';

    public function handle(): int
    {
        $serviceClass = $this->argument('service');

        if (!class_exists($serviceClass) || !is_subclass_of($serviceClass, ViewTrackingService::class)) {
            $this->error("'{$serviceClass}' is not a valid ViewTrackingService subclass.");
            return Command::FAILURE;
        }

        /** @var ViewTrackingService $service */
        $service = app($serviceClass);

        $this->info("Starting view count sync using {$serviceClass}...");

        if ($id = $this->option('id')) {
            return $this->syncSingle($service, (int) $id);
        }

        return $this->syncAll($service);
    }

    protected function syncSingle(ViewTrackingService $service, int $id): int
    {
        $this->info("Syncing view count for ID: {$id}");

        if ($service->syncToDatabase($id)) {
            $this->info("✓ Synced. Current view count: {$service->getViewCount($id)}");
            return Command::SUCCESS;
        }

        $this->error("✗ Failed to sync ID: {$id}");
        return Command::FAILURE;
    }

    protected function syncAll(ViewTrackingService $service): int
    {
        $results = $service->batchSyncAll();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Synced', $results['synced']],
                ['Failed', $results['failed']],
                ['Total',  $results['total']],
            ]
        );

        if (isset($results['error'])) {
            $this->error('Error: ' . $results['error']);
            return Command::FAILURE;
        }

        if ($results['failed'] > 0) {
            $this->warn('Some syncs failed. Check logs for details.');
            return Command::FAILURE;
        }

        $this->info('✓ All view counts synchronized successfully.');
        return Command::SUCCESS;
    }
}
