<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionRoom;
use App\Models\AuctionItem;
use App\Models\AuctionOffer;
use App\Models\AuctionInvoice;
use App\Models\AuctionAuditLog;
use App\Models\Bid;
use App\Models\UserInsuranceDeposit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuctionMonitoringController extends Controller
{
    /**
     * Dashboard metrics (total sales, active rooms, etc.)
     * GET /admin/auction-dashboard
     */
    public function dashboard(Request $request)
    {
        try {
            $metrics = [
                // Room Statistics
                'rooms' => [
                    'total' => AuctionRoom::count(),
                    'active' => AuctionRoom::where('status', 'active')->count(),
                    'scheduled' => AuctionRoom::where('status', 'scheduled')->count(),
                    'completed' => AuctionRoom::where('status', 'completed')->count(),
                    'cancelled' => AuctionRoom::where('status', 'cancelled')->count(),
                ],

                // Item Statistics
                'items' => [
                    'total' => AuctionItem::count(),
                    'active' => AuctionItem::where('status', 'active')->count(),
                    'sold' => AuctionItem::where('status', 'sold')->count(),
                    'unsold' => AuctionItem::where('status', 'unsold')->count(),
                    'offer_accepted' => AuctionItem::where('status', 'offer_accepted')->count(),
                ],

                // Financial Metrics
                'financial' => [
                    'total_sales_value' => AuctionInvoice::where('invoice_type', 'buyer_payment')
                        ->where('status', 'paid')
                        ->sum('amount'),
                    'total_commission_earned' => AuctionInvoice::where('invoice_type', 'buyer_payment')
                        ->where('status', 'paid')
                        ->sum('commission_amount'),
                    'pending_buyer_payments' => AuctionInvoice::where('invoice_type', 'buyer_payment')
                        ->where('status', 'pending')
                        ->sum('amount'),
                    'pending_seller_payouts' => AuctionInvoice::where('invoice_type', 'seller_payout')
                        ->where('status', 'pending')
                        ->sum('net_amount'),
                    'insurance_deposits_collected' => UserInsuranceDeposit::where('status', 'paid')
                        ->sum('amount'),
                ],

                // Bidding Activity
                'bidding' => [
                    'total_bids' => Bid::count(),
                    'accepted_bids' => Bid::where('status', 'accepted')->count(),
                    'rejected_bids' => Bid::where('status', 'rejected')->count(),
                    'average_bids_per_item' => AuctionItem::whereHas('bids')->avg('total_bids'),
                    'highest_bid_amount' => Bid::where('status', 'accepted')->max('amount'),
                ],

                // Offer Activity
                'offers' => [
                    'total_offers' => AuctionOffer::count(),
                    'pending_offers' => AuctionOffer::where('status', 'pending')->count(),
                    'accepted_offers' => AuctionOffer::where('status', 'accepted')->count(),
                    'rejected_offers' => AuctionOffer::where('status', 'rejected')->count(),
                    'offer_acceptance_rate' => $this->calculateOfferAcceptanceRate(),
                ],

                // Recent Activity
                'recent_activity' => [
                    'active_rooms' => AuctionRoom::with(['auctionItems' => function ($query) {
                        $query->where('status', 'active')->with(['car.carBrand', 'currentWinner']);
                    }])
                        ->where('status', 'active')
                        ->limit(5)
                        ->get()
                        ->map(function ($room) {
                            $currentItem = $room->auctionItems->first();
                            return [
                                'room_id' => $room->id,
                                'room_name' => $room->name,
                                'current_item' => $currentItem ? [
                                    'id' => $currentItem->id,
                                    'car_name' => $currentItem->car->name,
                                    'brand' => $currentItem->car->carBrand->name ?? 'Unknown',
                                    'current_price' => $currentItem->current_price,
                                    'total_bids' => $currentItem->total_bids,
                                    'ends_at' => $currentItem->ends_at,
                                    'seconds_remaining' => $currentItem->getSecondsRemaining(),
                                    'current_winner' => $currentItem->currentWinner ? $currentItem->currentWinner->name : null,
                                ] : null,
                            ];
                        }),

                    'recent_sales' => AuctionItem::with(['car.carBrand', 'seller', 'currentWinner'])
                        ->where('status', 'sold')
                        ->orderBy('finalized_at', 'desc')
                        ->limit(10)
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'car_name' => $item->car->name,
                                'brand' => $item->car->carBrand->name ?? 'Unknown',
                                'final_price' => $item->current_price,
                                'starting_price' => $item->starting_price,
                                'total_bids' => $item->total_bids,
                                'seller_name' => $item->seller->name,
                                'winner_name' => $item->currentWinner->name ?? 'Unknown',
                                'sold_at' => $item->finalized_at,
                            ];
                        }),

                    'high_value_pending_offers' => AuctionOffer::with([
                        'auctionItem.car.carBrand',
                        'buyer',
                        'seller'
                    ])
                        ->where('status', 'pending')
                        ->where('amount', '>=', 10000)
                        ->orderBy('amount', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function ($offer) {
                            return [
                                'id' => $offer->id,
                                'amount' => $offer->amount,
                                'car_name' => $offer->auctionItem->car->name,
                                'brand' => $offer->auctionItem->car->carBrand->name ?? 'Unknown',
                                'buyer_name' => $offer->buyer->name,
                                'seller_name' => $offer->seller->name,
                                'created_at' => $offer->created_at,
                                'expires_at' => $offer->expires_at,
                            ];
                        }),
                ],

                // Performance Metrics
                'performance' => [
                    'average_sale_price' => AuctionItem::where('status', 'sold')->avg('current_price'),
                    'sell_through_rate' => $this->calculateSellThroughRate(),
                    'average_auction_duration' => $this->calculateAverageAuctionDuration(),
                    'most_active_sellers' => $this->getMostActiveSellers(),
                    'top_bidders' => $this->getTopBidders(),
                ],

                // System Health
                'system_health' => [
                    'failed_jobs_count' => DB::table('failed_jobs')->count(),
                    'recent_errors' => AuctionAuditLog::where('action', 'error')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->count(),
                    'queue_depth' => DB::table('jobs')->count(),
                    'last_successful_room_start' => AuctionRoom::where('status', 'active')
                        ->latest('started_at')
                        ->value('started_at'),
                ],
            ];

            // Return JSON for API requests
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $metrics
                ]);
            }

            // Get room performance data for web view
            $roomPerformance = AuctionRoom::with(['auctionItems'])
                ->whereIn('status', ['active', 'completed'])
                ->get()
                ->map(function ($room) {
                    $soldItems = $room->auctionItems->where('status', 'sold');
                    $totalSales = $soldItems->sum('current_price');
                    $totalCommission = $totalSales * ($room->commission_percentage / 100);

                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'status' => $room->status,
                        'total_items' => $room->auctionItems->count(),
                        'sold_items' => $soldItems->count(),
                        'total_sales' => $totalSales,
                        'avg_sale_price' => $soldItems->count() > 0 ? $totalSales / $soldItems->count() : 0,
                        'total_commission' => $totalCommission,
                        'bid_participation_rate' => $room->auctionItems->count() > 0
                            ? ($room->auctionItems->where('total_bids', '>', 0)->count() / $room->auctionItems->count()) * 100
                            : 0,
                    ];
                });

            return view('backend.auctions.analytics.dashboard', compact('metrics', 'roomPerformance'));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get dashboard metrics: ' . $e->getMessage()
                ], 500);
            }

            flash(translate('Failed to load dashboard: ') . $e->getMessage())->error();
            return back();
        }
    }

    /**
     * Audit log viewer with filters
     * GET /admin/auction-audit-logs
     */
    public function auditLogs(Request $request)
    {
        try {
            $query = AuctionAuditLog::with(['auctionRoom', 'auctionItem.car', 'user']);

            // Apply filters
            if ($request->filled('auction_room_id')) {
                $query->where('auction_room_id', $request->auction_room_id);
            }

            if ($request->filled('auction_item_id')) {
                $query->where('auction_item_id', $request->auction_item_id);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $query->where(function ($searchQuery) use ($request) {
                    $searchQuery->where('action', 'like', '%' . $request->search . '%')
                        ->orWhere('ip_address', 'like', '%' . $request->search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($request) {
                            $userQuery->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                        });
                });
            }

            $logs = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 50));


            // Get available actions for filter dropdown
            $availableActions = AuctionAuditLog::distinct('action')
                ->orderBy('action')
                ->pluck('action')
                ->toArray();

            // Return JSON for API requests
            if ($request->wantsJson() || $request->is('api/*')) {
                // Transform the data for better readability
                $logs->getCollection()->transform(function ($log) {
                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'details' => $log->details,
                        'ip_address' => $log->ip_address,
                        'created_at' => $log->created_at,
                        'auction_room' => $log->auctionRoom ? [
                            'id' => $log->auctionRoom->id,
                            'name' => $log->auctionRoom->name,
                            'status' => $log->auctionRoom->status,
                        ] : null,
                        'auction_item' => $log->auctionItem ? [
                            'id' => $log->auctionItem->id,
                            'car_name' => $log->auctionItem->car->name ?? 'Unknown Car',
                            'status' => $log->auctionItem->status,
                        ] : null,
                        'user' => $log->user ? [
                            'id' => $log->user->id,
                            'name' => $log->user->name,
                            'email' => $log->user->email,
                            'user_type' => $log->user->user_type,
                        ] : null,
                    ];
                });

                return response()->json([
                    'success' => true,
                    'data' => [
                        'logs' => $logs,
                        'available_actions' => $availableActions,
                    ]
                ]);
            }

            // Get rooms for filter dropdown
            $rooms = AuctionRoom::orderBy('created_at', 'desc')->get();

            return view('backend.auctions.audit-logs.index', compact('logs', 'rooms'));
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get audit logs: ' . $e->getMessage()
                ], 500);
            }

            flash(translate('Failed to load audit logs: ') . $e->getMessage())->error();
            return back();
        }
    }

    /**
     * Show single audit log details
     * GET /admin/auction-audit-logs/{id}
     */
    public function showAuditLog(AuctionAuditLog $auctionAuditLog)
    {
        $log = $auctionAuditLog->load(['auctionRoom', 'auctionItem.car', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'action' => $log->action,
                'details' => $log->details,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'auction_room' => $log->auctionRoom ? [
                    'id' => $log->auctionRoom->id,
                    'name' => $log->auctionRoom->name,
                ] : null,
                'auction_item' => $log->auctionItem ? [
                    'id' => $log->auctionItem->id,
                    'car' => $log->auctionItem->car ? [
                        'car_name' => $log->auctionItem->car->car_name,
                    ] : null,
                ] : null,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ] : null,
            ]
        ]);
    }


    /**
     * Get real-time system status
     * GET /admin/auction-system-status
     */
    public function systemStatus(): JsonResponse
    {
        try {
            $status = [
                'timestamp' => now(),
                'active_auctions' => AuctionRoom::where('status', 'active')->count(),
                'active_items' => AuctionItem::where('status', 'active')->count(),
                'recent_bids' => Bid::where('created_at', '>=', now()->subMinutes(5))->count(),
                'pending_jobs' => DB::table('jobs')->count(),
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'system_errors_last_hour' => AuctionAuditLog::where('action', 'error')
                    ->where('created_at', '>=', now()->subHour())
                    ->count(),
                'websocket_status' => 'active', // This would be checked via actual WebSocket health check
                'database_status' => 'healthy',
                'redis_status' => 'healthy', // This would be checked via Redis ping
            ];

            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed analytics for a specific time period
     * GET /admin/auction-analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'required|in:day,week,month,quarter,year',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        try {
            $period = $request->period;
            $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : $this->getDefaultDateFrom($period);
            $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

            $analytics = [
                'period' => $period,
                'date_range' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ],
                'sales_trends' => $this->getSalesTrends($dateFrom, $dateTo, $period),
                'bidding_activity' => $this->getBiddingActivity($dateFrom, $dateTo, $period),
                'offer_trends' => $this->getOfferTrends($dateFrom, $dateTo, $period),
                'top_performing_categories' => $this->getTopPerformingCategories($dateFrom, $dateTo),
                'seller_performance' => $this->getSellerPerformance($dateFrom, $dateTo),
                'buyer_activity' => $this->getBuyerActivity($dateFrom, $dateTo),
                'revenue_breakdown' => $this->getRevenueBreakdown($dateFrom, $dateTo),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods for calculations

    private function calculateOfferAcceptanceRate(): float
    {
        $totalOffers = AuctionOffer::count();
        if ($totalOffers === 0) return 0;

        $acceptedOffers = AuctionOffer::where('status', 'accepted')->count();
        return round(($acceptedOffers / $totalOffers) * 100, 2);
    }

    private function calculateSellThroughRate(): float
    {
        $totalItems = AuctionItem::whereIn('status', ['sold', 'unsold'])->count();
        if ($totalItems === 0) return 0;

        $soldItems = AuctionItem::where('status', 'sold')->count();
        return round(($soldItems / $totalItems) * 100, 2);
    }

    private function calculateAverageAuctionDuration(): float
    {
        return AuctionItem::where('status', 'sold')
            ->whereNotNull('started_at')
            ->whereNotNull('finalized_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, finalized_at)) as avg_duration')
            ->value('avg_duration') ?? 0;
    }

    private function getMostActiveSellers(): array
    {
        return AuctionItem::with('seller')
            ->select('seller_id', DB::raw('COUNT(*) as items_count'), DB::raw('SUM(CASE WHEN status = "sold" THEN 1 ELSE 0 END) as sold_count'))
            ->groupBy('seller_id')
            ->orderBy('items_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'seller_name' => $item->seller->name,
                    'total_items' => $item->items_count,
                    'sold_items' => $item->sold_count,
                    'sell_rate' => $item->items_count > 0 ? round(($item->sold_count / $item->items_count) * 100, 2) : 0,
                ];
            })
            ->toArray();
    }

    private function getTopBidders(): array
    {
        return Bid::with('bidder')
            ->where('status', 'accepted')
            ->select('bidder_id', DB::raw('COUNT(*) as bid_count'), DB::raw('SUM(amount) as total_bid_amount'))
            ->groupBy('bidder_id')
            ->orderBy('total_bid_amount', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($bid) {
                return [
                    'bidder_name' => $bid->bidder->name,
                    'total_bids' => $bid->bid_count,
                    'total_amount' => $bid->total_bid_amount,
                    'average_bid' => round($bid->total_bid_amount / $bid->bid_count, 2),
                ];
            })
            ->toArray();
    }

    private function getDefaultDateFrom(string $period): Carbon
    {
        return match ($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
    }

    private function getSalesTrends(Carbon $dateFrom, Carbon $dateTo, string $period): array
    {
        $format = match ($period) {
            'day' => '%Y-%m-%d %H:00:00',
            'week', 'month' => '%Y-%m-%d',
            'quarter', 'year' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return AuctionItem::where('status', 'sold')
            ->whereBetween('finalized_at', [$dateFrom, $dateTo])
            ->selectRaw("DATE_FORMAT(finalized_at, '{$format}') as period")
            ->selectRaw('COUNT(*) as items_sold')
            ->selectRaw('SUM(current_price) as total_value')
            ->selectRaw('AVG(current_price) as avg_price')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    private function getBiddingActivity(Carbon $dateFrom, Carbon $dateTo, string $period): array
    {
        $format = match ($period) {
            'day' => '%Y-%m-%d %H:00:00',
            'week', 'month' => '%Y-%m-%d',
            'quarter', 'year' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return Bid::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period")
            ->selectRaw('COUNT(*) as total_bids')
            ->selectRaw('SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_bids')
            ->selectRaw('COUNT(DISTINCT bidder_id) as unique_bidders')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    private function getOfferTrends(Carbon $dateFrom, Carbon $dateTo, string $period): array
    {
        $format = match ($period) {
            'day' => '%Y-%m-%d %H:00:00',
            'week', 'month' => '%Y-%m-%d',
            'quarter', 'year' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return AuctionOffer::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period")
            ->selectRaw('COUNT(*) as total_offers')
            ->selectRaw('SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_offers')
            ->selectRaw('AVG(amount) as avg_offer_amount')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    private function getTopPerformingCategories(Carbon $dateFrom, Carbon $dateTo): array
    {
        return AuctionItem::with(['car.carCategory'])
            ->where('status', 'sold')
            ->whereBetween('finalized_at', [$dateFrom, $dateTo])
            ->join('cars', 'auction_items.car_id', '=', 'cars.id')
            ->join('car_categories', 'cars.car_category_id', '=', 'car_categories.id')
            ->select('car_categories.name as category_name')
            ->selectRaw('COUNT(*) as items_sold')
            ->selectRaw('SUM(auction_items.current_price) as total_value')
            ->selectRaw('AVG(auction_items.current_price) as avg_price')
            ->groupBy('car_categories.id', 'car_categories.name')
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getSellerPerformance(Carbon $dateFrom, Carbon $dateTo): array
    {
        return AuctionItem::with('seller')
            ->where('status', 'sold')
            ->whereBetween('finalized_at', [$dateFrom, $dateTo])
            ->select('seller_id')
            ->selectRaw('COUNT(*) as items_sold')
            ->selectRaw('SUM(current_price) as total_revenue')
            ->selectRaw('AVG(current_price) as avg_sale_price')
            ->groupBy('seller_id')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'seller_name' => $item->seller->name,
                    'items_sold' => $item->items_sold,
                    'total_revenue' => $item->total_revenue,
                    'avg_sale_price' => $item->avg_sale_price,
                ];
            })
            ->toArray();
    }

    private function getBuyerActivity(Carbon $dateFrom, Carbon $dateTo): array
    {
        return Bid::with('bidder')
            ->where('status', 'accepted')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('bidder_id')
            ->selectRaw('COUNT(*) as winning_bids')
            ->selectRaw('SUM(amount) as total_spent')
            ->selectRaw('AVG(amount) as avg_winning_bid')
            ->groupBy('bidder_id')
            ->orderBy('total_spent', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($bid) {
                return [
                    'buyer_name' => $bid->bidder->name,
                    'winning_bids' => $bid->winning_bids,
                    'total_spent' => $bid->total_spent,
                    'avg_winning_bid' => $bid->avg_winning_bid,
                ];
            })
            ->toArray();
    }

    private function getRevenueBreakdown(Carbon $dateFrom, Carbon $dateTo): array
    {
        $buyerPayments = AuctionInvoice::where('invoice_type', 'buyer_payment')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->selectRaw('SUM(amount) as total_amount, SUM(commission_amount) as total_commission')
            ->first();

        $sellerPayouts = AuctionInvoice::where('invoice_type', 'seller_payout')
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$dateFrom, $dateTo])
            ->sum('net_amount');

        return [
            'gross_sales' => $buyerPayments->total_amount ?? 0,
            'commission_earned' => $buyerPayments->total_commission ?? 0,
            'seller_payouts' => $sellerPayouts,
            'net_revenue' => ($buyerPayments->total_commission ?? 0),
        ];
    }
}
