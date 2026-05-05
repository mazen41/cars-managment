<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

abstract class ViewTrackingService
{
    /**
     * Time window for duplicate view prevention (in seconds)
     */
    const DUPLICATE_PREVENTION_WINDOW = 3600; // 1 hour

    /**
     * Batch size for syncing views from Redis to database
     */
    const BATCH_SYNC_THRESHOLD = 10;

    /**
     * The model class this service tracks (e.g. Car::class)
     */
    abstract protected function getModelClass(): string;

    /**
     * The database table for the model
     */
    abstract protected function getTable(): string;

    /**
     * The Redis key namespace (e.g. 'car', 'customer_product')
     */
    abstract protected function getNamespace(): string;

    // -------------------------------------------------------------------------
    // Redis key helpers
    // -------------------------------------------------------------------------

    protected function viewCountKey(int $id): string
    {
        return "{$this->getNamespace()}:views:{$id}";
    }

    protected function dailyViewKey(int $id, string $date): string
    {
        return "{$this->getNamespace()}:views:daily:{$id}:{$date}";
    }

    protected function userViewKey(int $id, string $viewerIdentifier): string
    {
        return "{$this->getNamespace()}:user_view:{$id}:{$viewerIdentifier}";
    }

    protected function pendingSyncKey(int $id): string
    {
        return "{$this->getNamespace()}:pending_sync:{$id}";
    }

    protected function pendingSyncPattern(): string
    {
        return "{$this->getNamespace()}:pending_sync:*";
    }

    // -------------------------------------------------------------------------
    // Core tracking logic
    // -------------------------------------------------------------------------

    /**
     * Record a view with duplicate prevention.
     */
    public function recordView(
        Model $model,
        ?string $userId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): bool {
        try {
            $viewerIdentifier = $this->generateViewerIdentifier($userId, $ipAddress, $userAgent);

            if ($this->isDuplicateView($model->id, $viewerIdentifier)) {
                return false;
            }

            $this->markViewerAsViewed($model->id, $viewerIdentifier);
            $this->incrementViewCounters($model->id);
            $this->syncIfNeeded($model->id);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to record {$this->getNamespace()} view", [
                'id'    => $model->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    protected function generateViewerIdentifier(
        ?string $userId,
        ?string $ipAddress,
        ?string $userAgent
    ): string {
        if ($userId) {
            return "user:{$userId}";
        }

        $userAgentHash = $userAgent ? substr(md5($userAgent), 0, 8) : 'unknown';
        return "anon:{$ipAddress}:{$userAgentHash}";
    }

    protected function isDuplicateView(int $id, string $viewerIdentifier): bool
    {
        return Redis::exists($this->userViewKey($id, $viewerIdentifier)) > 0;
    }

    protected function markViewerAsViewed(int $id, string $viewerIdentifier): void
    {
        Redis::setex($this->userViewKey($id, $viewerIdentifier), static::DUPLICATE_PREVENTION_WINDOW, 1);
    }

    protected function incrementViewCounters(int $id): void
    {
        Redis::incr($this->viewCountKey($id));

        $dailyKey = $this->dailyViewKey($id, date('Y-m-d'));
        Redis::incr($dailyKey);
        Redis::expire($dailyKey, 86400 * 7); // keep 7 days

        Redis::incr($this->pendingSyncKey($id));
    }

    protected function syncIfNeeded(int $id): void
    {
        $pendingCount = (int) Redis::get($this->pendingSyncKey($id));

        if ($pendingCount >= static::BATCH_SYNC_THRESHOLD) {
            $this->syncToDatabase($id);
        }
    }

    // -------------------------------------------------------------------------
    // Database sync
    // -------------------------------------------------------------------------

    public function syncToDatabase(int $id): bool
    {
        try {
            $redisViewCount = (int) Redis::get($this->viewCountKey($id));

            if ($redisViewCount > 0) {
                DB::table($this->getTable())
                    ->where('id', $id)
                    ->update(['view_count' => $redisViewCount]);

                Redis::set($this->pendingSyncKey($id), 0);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Failed to sync {$this->getNamespace()} view count to database", [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function batchSyncAll(): array
    {
        $synced = 0;
        $failed = 0;

        try {
            $keys = Redis::keys($this->pendingSyncPattern());

            foreach ($keys as $key) {
                $pendingCount = (int) Redis::get($key);

                if ($pendingCount > 0) {
                    $id = (int) str_replace("{$this->getNamespace()}:pending_sync:", '', $key);

                    if ($this->syncToDatabase($id)) {
                        $synced++;
                    } else {
                        $failed++;
                    }
                }
            }

            return ['synced' => $synced, 'failed' => $failed, 'total' => $synced + $failed];
        } catch (\Exception $e) {
            Log::error("Batch sync failed for {$this->getNamespace()}", ['error' => $e->getMessage()]);

            return ['synced' => $synced, 'failed' => $failed, 'error' => $e->getMessage()];
        }
    }

    // -------------------------------------------------------------------------
    // Read helpers
    // -------------------------------------------------------------------------

    public function getViewCount(int $id): int
    {
        return (int) Redis::get($this->viewCountKey($id)) ?: 0;
    }

    public function getDailyViewCount(int $id, ?string $date = null): int
    {
        $date = $date ?: date('Y-m-d');
        return (int) Redis::get($this->dailyViewKey($id, $date)) ?: 0;
    }

    public function getViewStatistics(int $id): array
    {
        return [
            'total_views'     => $this->getViewCount($id),
            'today_views'     => $this->getDailyViewCount($id),
            'yesterday_views' => $this->getDailyViewCount($id, date('Y-m-d', strtotime('-1 day'))),
            'last_7_days'     => $this->getViewsForPeriod($id, 7),
        ];
    }

    protected function getViewsForPeriod(int $id, int $days): int
    {
        $total = 0;

        for ($i = 0; $i < $days; $i++) {
            $total += $this->getDailyViewCount($id, date('Y-m-d', strtotime("-{$i} days")));
        }

        return $total;
    }

    /**
     * Seed Redis from the current database value (useful after Redis data loss).
     */
    public function initializeFromDatabase(int $id): void
    {
        $model = ($this->getModelClass())::find($id);

        if ($model) {
            Redis::set($this->viewCountKey($id), $model->view_count ?? 0);
        }
    }
}
