<?php

namespace App\Services;

use App\Models\AuctionRoom;
use App\Models\AuctionItem;
use App\Models\Bid;
use App\Models\AuctionOffer;
use App\Models\AuctionAuditLog;
use App\Models\UserInsuranceDeposit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuctionRoomReportService
{
    /**
     * Cache TTL for completed room reports (24 hours)
     */
    const CACHE_TTL = 86400;

    /**
     * Get comprehensive report data for an auction room
     *
     * @param AuctionRoom $room
     * @param bool $useCache
     * @return array
     */
    public function getReportData(AuctionRoom $room, bool $useCache = true): mixed
    {
        // Only cache completed rooms as their data is immutable
        if ($useCache && $room->status === 'completed') {
            $cacheKey = $this->getCacheKey($room->id);

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($room) {
                return $this->generateReportData($room);
            });
        }

        return $this->generateReportData($room);
    }

    /**
     * Generate report data with optimized queries
     *
     * @param AuctionRoom $room
     * @return array
     */
    protected function generateReportData(AuctionRoom $room): array
    {
        // Eager load all relationships in a single optimized query
        $room->load([
            'currency',
            'creator',
            'auctionItems' => function ($query) {
                $query->orderBy('sequence_order', 'asc');
            },
            'auctionItems.car.carBrand',
            'auctionItems.car.carModel',
            'auctionItems.seller',
            'auctionItems.currentWinner',
            'auctionItems.bids' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'auctionItems.bids.bidder',
            'auctionItems.auctionOffers.buyer',
            'auctionItems.auctionOffers.seller',
        ]);

        // Load audit logs separately with user relationship
        $auditLogs = AuctionAuditLog::where('auction_room_id', $room->id)
            ->with('user', 'auctionItem')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get all bids across all items (already loaded via eager loading)
        $allBids = $room->auctionItems->flatMap(function ($item) {
            return $item->bids;
        });

        // Get all offers across all items (already loaded via eager loading)
        $allOffers = $room->auctionItems->flatMap(function ($item) {
            return $item->auctionOffers;
        });


        return [
            'room' => $room,
            'overview' => $this->buildOverview($room),
            'financial_summary' => $this->calculateFinancialSummary($room),
            'items' => $room->auctionItems,
            'bids' => $allBids,
            'offers' => $allOffers,
            'participants' => $this->calculateParticipantStatistics($room, $allBids),
            'timing' => $this->calculateTimingStatistics($room),
            'audit_log' => $auditLogs,
        ];
    }

    /**
     * Build overview section data
     *
     * @param AuctionRoom $room
     * @return array
     */
    protected function buildOverview(AuctionRoom $room): array
    {
        return [
            'name' => $room->name,
            'description' => $room->description,
            'status' => $room->status,
            'scheduled_start_at' => $room->scheduled_start_at,
            'started_at' => $room->started_at,
            'completed_at' => $room->completed_at,
            'total_duration' => $room->started_at && $room->completed_at
                ? $room->started_at->diffInSeconds($room->completed_at)
                : 0,
            'total_items' => $room->auctionItems->count(),
            'configuration' => [
                'commission_percentage' => $room->commission_percentage,
                'bid_increment_type' => $room->bid_increment_type,
                'bid_increment_value' => $room->bid_increment_value,
                'base_timer_seconds' => $room->base_timer_seconds,
                'extension_seconds' => $room->extension_seconds,
                'insurance_deposit_amount' => $room->insurance_deposit_amount,
                'currency' => $room->currency ? $room->currency->code : null,
            ],
        ];
    }

    /**
     * Calculate financial summary for the auction room
     *
     * @param AuctionRoom $room
     * @param Collection|null $insuranceDeposits
     * @return array
     */
    public function calculateFinancialSummary(AuctionRoom $room): array
    {
        $soldItems = $room->auctionItems->where('status', 'sold');

        $totalSales = $soldItems->sum('current_price');

        // Calculate commission for each sold item
        $totalCommission = $soldItems->sum(function ($item) use ($room) {
            return $item->current_price * ($room->commission_percentage / 100);
        });

        // Calculate net revenue (sales + commission)
        $netRevenue = $totalSales + $totalCommission;

        return [
            'total_sales' => round($totalSales, 2),
            'total_commission' => round($totalCommission, 2),
            'net_revenue' => round($netRevenue, 2),
        ];
    }

    /**
     * Calculate participant statistics
     *
     * @param AuctionRoom $room
     * @param Collection $allBids
     * @param Collection|null $insuranceDeposits
     * @return array
     */
    public function calculateParticipantStatistics(AuctionRoom $room, Collection $allBids): array
    {
        // Count unique bidders from bids
        $uniqueBidders = $allBids->pluck('bidder_id')->unique()->count();

        $registeredParticipants = 0;

        // Get winners - all items that are sold with a winner
        $winners = $room->auctionItems
            ->where('status', 'sold')
            ->whereNotNull('current_winner_id')
            ->map(function ($item) {
                return [
                    'user' => $item->currentWinner,
                    'item' => $item,
                    'amount' => $item->current_price,
                ];
            });

        // Calculate average bids per item
        $totalItems = $room->auctionItems->count();
        $totalBids = $allBids->count();
        $averageBidsPerItem = $totalItems > 0 ? $totalBids / $totalItems : 0;

        // Calculate participation rate (unique bidders / registered participants * 100)
        $participationRate = $registeredParticipants > 0
            ? ($uniqueBidders / $registeredParticipants) * 100
            : 0;

        return [
            'total_registered' => $registeredParticipants,
            'total_bidders' => $uniqueBidders,
            'total_winners' => $winners->count(),
            'participation_rate' => round($participationRate, 2),
            'average_bids_per_item' => round($averageBidsPerItem, 2),
            'winners' => $winners,
        ];
    }

    /**
     * Calculate timing statistics
     *
     * @param AuctionRoom $room
     * @return array
     */
    public function calculateTimingStatistics(AuctionRoom $room): array
    {
        $totalDuration = 0;
        if ($room->started_at && $room->completed_at) {
            $totalDuration = $room->started_at->diffInSeconds($room->completed_at);
        }

        $totalExtensions = $room->auctionItems->sum('total_extensions');
        $itemsWithExtensions = $room->auctionItems->where('total_extensions', '>', 0)->count();

        $completedItems = $room->auctionItems->whereIn('status', ['sold', 'unsold'])->count();
        $averageTimePerItem = $completedItems > 0 ? $totalDuration / $completedItems : 0;

        return [
            'total_duration' => $totalDuration,
            'average_time_per_item' => round($averageTimePerItem, 2),
            'total_extensions' => $totalExtensions,
            'items_with_extensions' => $itemsWithExtensions,
            'scheduled_vs_actual' => [
                'scheduled' => $room->scheduled_start_at,
                'actual' => $room->started_at,
                'difference_seconds' => $room->scheduled_start_at && $room->started_at
                    ? $room->scheduled_start_at->diffInSeconds($room->started_at, false)
                    : 0,
            ],
        ];
    }

    /**
     * Apply filters to a collection
     *
     * @param Collection $data
     * @param array $filters
     * @param string $type
     * @return Collection
     */
    public function applyFilters(Collection $data, array $filters, string $type): Collection
    {
        switch ($type) {
            case 'bids':
                return $this->filterBids($data, $filters);
            case 'offers':
                return $this->filterOffers($data, $filters);
            case 'audit_logs':
                return $this->filterAuditLogs($data, $filters);
            case 'items':
                return $this->filterItems($data, $filters);
            default:
                return $data;
        }
    }

    /**
     * Filter bids collection
     *
     * @param Collection $bids
     * @param array $filters
     * @return Collection
     */
    protected function filterBids(Collection $bids, array $filters): Collection
    {
        if (isset($filters['status'])) {
            $bids = $bids->where('status', $filters['status']);
        }

        if (isset($filters['bidder_id'])) {
            $bids = $bids->where('bidder_id', $filters['bidder_id']);
        }

        if (isset($filters['bidder_name'])) {
            $searchTerm = strtolower($filters['bidder_name']);
            $bids = $bids->filter(function ($bid) use ($searchTerm) {
                return $bid->bidder &&
                       (stripos($bid->bidder->name, $searchTerm) !== false ||
                        stripos($bid->bidder->email, $searchTerm) !== false);
            });
        }

        if (isset($filters['date_from'])) {
            $bids = $bids->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $bids = $bids->where('created_at', '<=', $filters['date_to']);
        }

        return $bids;
    }

    /**
     * Filter offers collection
     *
     * @param Collection $offers
     * @param array $filters
     * @return Collection
     */
    protected function filterOffers(Collection $offers, array $filters): Collection
    {
        if (isset($filters['status'])) {
            $offers = $offers->where('status', $filters['status']);
        }

        if (isset($filters['buyer_id'])) {
            $offers = $offers->where('buyer_id', $filters['buyer_id']);
        }

        if (isset($filters['buyer_name'])) {
            $searchTerm = strtolower($filters['buyer_name']);
            $offers = $offers->filter(function ($offer) use ($searchTerm) {
                return $offer->buyer &&
                       (stripos($offer->buyer->name, $searchTerm) !== false ||
                        stripos($offer->buyer->email, $searchTerm) !== false);
            });
        }

        if (isset($filters['seller_id'])) {
            $offers = $offers->where('seller_id', $filters['seller_id']);
        }

        if (isset($filters['seller_name'])) {
            $searchTerm = strtolower($filters['seller_name']);
            $offers = $offers->filter(function ($offer) use ($searchTerm) {
                return $offer->seller &&
                       (stripos($offer->seller->name, $searchTerm) !== false ||
                        stripos($offer->seller->email, $searchTerm) !== false);
            });
        }

        return $offers;
    }

    /**
     * Filter audit logs collection
     *
     * @param Collection $logs
     * @param array $filters
     * @return Collection
     */
    protected function filterAuditLogs(Collection $logs, array $filters): Collection
    {
        if (isset($filters['action'])) {
            $logs = $logs->where('action', $filters['action']);
        }

        if (isset($filters['user_id'])) {
            $logs = $logs->where('user_id', $filters['user_id']);
        }

        if (isset($filters['user_name'])) {
            $searchTerm = strtolower($filters['user_name']);
            $logs = $logs->filter(function ($log) use ($searchTerm) {
                return $log->user &&
                       (stripos($log->user->name, $searchTerm) !== false ||
                        stripos($log->user->email, $searchTerm) !== false);
            });
        }

        if (isset($filters['date_from'])) {
            $logs = $logs->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $logs = $logs->where('created_at', '<=', $filters['date_to']);
        }

        return $logs;
    }

    /**
     * Filter items collection
     *
     * @param Collection $items
     * @param array $filters
     * @return Collection
     */
    protected function filterItems(Collection $items, array $filters): Collection
    {
        if (isset($filters['status'])) {
            $items = $items->where('status', $filters['status']);
        }

        if (isset($filters['min_price'])) {
            $items = $items->where('current_price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $items = $items->where('current_price', '<=', $filters['max_price']);
        }

        return $items;
    }

    /**
     * Get cache key for a room report
     *
     * @param int $roomId
     * @return string
     */
    protected function getCacheKey(int $roomId): string
    {
        return "auction_report:{$roomId}";
    }

    /**
     * Clear cached report data for a room
     *
     * @param int $roomId
     * @return bool
     */
    public function clearCache(int $roomId): bool
    {
        $cacheKey = $this->getCacheKey($roomId);
        return Cache::forget($cacheKey);
    }

    /**
     * Get PDF cache key for a room report
     *
     * @param int $roomId
     * @return string
     */
    protected function getPdfCacheKey(int $roomId): string
    {
        return "auction_report_pdf:{$roomId}";
    }

    /**
     * Clear cached PDF for a room
     *
     * @param int $roomId
     * @return bool
     */
    public function clearPdfCache(int $roomId): bool
    {
        $pdfCacheKey = $this->getPdfCacheKey($roomId);
        return Cache::forget($pdfCacheKey);
    }
}
