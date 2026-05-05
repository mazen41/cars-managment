<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionOffer;
use App\Models\AuctionRoom;
use App\Services\AuctionOfferService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuctionOfferController extends Controller
{
    protected AuctionOfferService $auctionOfferService;

    public function __construct(AuctionOfferService $auctionOfferService)
    {
        $this->auctionOfferService = $auctionOfferService;
    }


    /**
     * Admin force accept offer
     * POST /admin/auction-offers/{id}/force-accept
     */
    public function forceAccept(Request $request, AuctionOffer $auctionOffer): JsonResponse
    {
        $request->validate([
            'admin_reason' => 'required|string|max:500'
        ]);

        try {
            if ($auctionOffer->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending offers can be force accepted'
                ], 422);
            }

            if (!$auctionOffer->canBeAccepted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This offer cannot be accepted (expired or auction already started)'
                ], 422);
            }

            $admin = Auth::user();
            $success = $this->auctionOfferService->acceptOffer(
                $auctionOffer,
                $admin,
                'Admin force accepted: ' . $request->admin_reason
            );

            if(!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to force accept offer'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Offer force accepted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to force accept offer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin force reject offer
     * POST /admin/auction-offers/{id}/force-reject
     */
    public function forceReject(Request $request, AuctionOffer $auctionOffer): JsonResponse
    {
        $request->validate([
            'admin_reason' => 'required|string|max:500'
        ]);

        try {
            if ($auctionOffer->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending offers can be force rejected'
                ], 422);
            }

            $admin = Auth::user();
            $success = $this->auctionOfferService->rejectOffer(
                $auctionOffer,
                $admin,
                'Admin force rejected: ' . $request->admin_reason
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Offer force rejected successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to force reject offer'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to force reject offer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display offers index view for admin
     * GET /admin/auction-offers/view
     */
    public function index(Request $request): View
    {
        // Build query with eager loading to avoid N+1 queries
        $query = AuctionOffer::with([
            'auctionItem.car.carBrand',
            'auctionItem.car.carModel',
            'auctionItem.auctionRoom',
            'buyer',
            'seller'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('auction_room_id')) {
            $query->whereHas('auctionItem', function ($itemQuery) use ($request) {
                $itemQuery->where('auction_room_id', $request->auction_room_id);
            });
        }

        if($request->filled('auction_item_id')){
            $query->whereHas('auctionItem', function($itemQuery) use ($request){
                $itemQuery->where('id', $request->auction_item_id);
            });
        }

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('auctionItem.car', function ($carQuery) use ($searchTerm) {
                    $carQuery->where('name', 'like', '%' . $searchTerm . '%');
                })
                ->orWhereHas('buyer', function ($buyerQuery) use ($searchTerm) {
                    $buyerQuery->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%');
                })
                ->orWhereHas('seller', function ($sellerQuery) use ($searchTerm) {
                    $sellerQuery->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('email', 'like', '%' . $searchTerm . '%');
                });
            });
        }

        // Get paginated offers
        $offers = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->except('page'));

        // Get statistics for the cards
        $stats = [
            'total' => AuctionOffer::count(),
            'pending' => AuctionOffer::where('status', 'pending')->count(),
            'accepted' => AuctionOffer::where('status', 'accepted')->count(),
            'rejected' => AuctionOffer::where('status', 'rejected')->count(),
            'expired' => AuctionOffer::where('status', 'expired')->count(),
            'withdrawn' => AuctionOffer::where('status', 'withdrawn')->count(),
        ];

        // Get auction rooms for filter dropdown
        $auctionRooms = AuctionRoom::orderBy('name')->get();

        return view('backend.auctions.offers.index', compact('offers', 'stats', 'auctionRooms'));
    }

    /**
     * Display offer detail view for admin
     * GET /admin/auction-offers/{id}/view
     */
    public function show(AuctionOffer $auctionOffer): View
    {
        // Load all necessary relationships with eager loading
        $offer = $auctionOffer->load([
            'auctionItem.car.carBrand',
            'auctionItem.car.carModel',
            'auctionItem.car.carColor',
            'auctionItem.auctionRoom',
            'buyer',
            'seller'
        ]);

        // Prepare structured data for the view
        $offerDetails = [
            'car_details' => [
                'name' => $offer->auctionItem->car_name,
                'brand' => $offer->auctionItem->car->carBrand->name ?? 'Unknown',
                'model' => $offer->auctionItem->car->carModel->name ?? 'Unknown',
                'year' => $offer->auctionItem->car->manufacture_year,
                'color' => $offer->auctionItem->car->carColor->name ?? 'Unknown',
                'mileage' => $offer->auctionItem->car->milage,
                'condition' => $offer->auctionItem->car->condition,
                'main_photo_url' => $offer->auctionItem->car->main_photo_url,
            ],
            'auction_context' => [
                'room_name' => $offer->auctionItem->auctionRoom->name,
                'room_status' => $offer->auctionItem->auctionRoom->status,
                'item_starting_price' => $offer->auctionItem->starting_price,
                'item_current_price' => $offer->auctionItem->current_price,
                'item_reserve_price' => $offer->auctionItem->reserve_price ?? null,
                'item_status' => $offer->auctionItem->status,
            ],
            'participants' => [
                'buyer' => [
                    'id' => $offer->buyer->id,
                    'name' => $offer->buyer->name,
                    'email' => $offer->buyer->email,
                    'has_insurance_deposit' => method_exists($offer->buyer, 'hasInsuranceDeposit')
                        ? $offer->buyer->hasInsuranceDeposit()
                        : false,
                ],
                'seller' => [
                    'id' => $offer->seller->id,
                    'name' => $offer->seller->name,
                    'email' => $offer->seller->email,
                    'total_cars_sold' => method_exists($offer->seller, 'sellerAuctionItems')
                        ? $offer->seller->sellerAuctionItems()->where('status', 'sold')->count()
                        : 0,
                ]
            ],
            'offer_analysis' => [
                'percentage_of_starting_price' => $offer->auctionItem->starting_price > 0
                    ? round(($offer->amount / $offer->auctionItem->starting_price) * 100, 2)
                    : 0,
                'is_above_reserve' => $offer->auctionItem->reserve_price
                    ? $offer->amount >= $offer->auctionItem->reserve_price
                    : true,
                'can_be_accepted' => $offer->canBeAccepted(),
                'is_expired' => $offer->expires_at && $offer->expires_at->isPast(),
                'days_since_created' => $offer->created_at->diffInDays(now()),
            ]
        ];

        return view('backend.auctions.offers.show', compact('offer', 'offerDetails'));
    }

    /**
     * Display offer statistics view for admin
     * GET /admin/auction-offers/stats/view
     */
    public function stats(): View
    {
        // Gather all statistics data
        $stats = [
            'total_offers' => AuctionOffer::count(),
            'pending_offers' => AuctionOffer::where('status', 'pending')->count(),
            'accepted_offers' => AuctionOffer::where('status', 'accepted')->count(),
            'rejected_offers' => AuctionOffer::where('status', 'rejected')->count(),
            'expired_offers' => AuctionOffer::where('status', 'expired')->count(),
            'withdrawn_offers' => AuctionOffer::where('status', 'withdrawn')->count(),
            'total_offer_value' => AuctionOffer::where('status', 'accepted')->sum('amount'),
            'average_offer_amount' => AuctionOffer::avg('amount') ?? 0,
            'highest_offer' => AuctionOffer::max('amount') ?? 0,
            'recent_high_value_offers' => AuctionOffer::with([
                'auctionItem.car.carBrand',
                'buyer',
                'seller'
            ])
                ->where('status', 'pending')
                ->where('amount', '>=', 10000)
                ->orderBy('amount', 'desc')
                ->limit(5)
                ->get(),
            'offers_by_status' => AuctionOffer::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'monthly_offer_trends' => AuctionOffer::selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as total_offers,
                    SUM(CASE WHEN status = "accepted" THEN 1 ELSE 0 END) as accepted_offers,
                    AVG(amount) as avg_amount
                ')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->toArray()
        ];

        return view('backend.auctions.offers.stats', compact('stats'));
    }
}
