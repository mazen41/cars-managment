<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlaceBidRequest;
use App\Http\Resources\V2\Auction\BidResource;
use App\Models\AuctionItem;
use App\Models\Bid;
use App\Services\AuctionBiddingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuctionBiddingController extends Controller
{
    public function __construct(
        private AuctionBiddingService $auctionBiddingService
    ) {}

    /**
     * Place bid with validation and rate limiting
     * POST /api/v2/auction-items/{id}/bids
     */
    public function store(AuctionItem $auctionItem, PlaceBidRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $amount = $request->validated('amount');
            $bidToken = $request->validated('bid_token', Str::uuid()->toString());

            // Check if user can bid
            if (!$user->canBid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insurance deposit required',
                    'data' => [
                        'has_deposit' => $user->hasInsuranceDeposit()
                    ]
                ], 403);
            }

            // Check if item can receive bids
            if (!$auctionItem->canReceiveBids()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This item is not currently accepting bids',
                    'data' => [
                        'item_status' => $auctionItem->status,
                        'current_price' => $auctionItem->current_price
                    ]
                ], 409);
            }

            // Validate bid amount
            $validation = $this->auctionBiddingService->validateBid($auctionItem, $user, $amount);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message'] ?? $validation['reason'],
                    'data' => [
                        'minimum_bid' => $validation['minimum_bid'] ?? null,
                        'current_price' => $auctionItem->current_price
                    ]
                ], 422);
            }

            // Place the bid
            $bid = $this->auctionBiddingService->placeBid($auctionItem, $user, $amount, $bidToken);
            dispatch(new \App\Jobs\ProcessBidJob($bid->id));
            return response()->json([
                'success' => true,
                'message' => 'Bid placed successfully',
                'data' => $bid->load('auctionItem')
            ], 201);

        } catch (\Exception $e) {
            // Handle duplicate bid (idempotency)
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'bid_token')) {
                $existingBid = Bid::where('bid_token', $bidToken)
                    ->where('bidder_id', $user->id)
                    ->first();

                if ($existingBid) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Bid already exists',
                        'data' => $existingBid->load('auctionItem')
                    ]);
                }
            }

            // Handle concurrent bid conflict
            if (str_contains($e->getMessage(), 'Another bid was placed')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => [
                        'current_price' => $auctionItem->fresh()->current_price,
                        'minimum_bid' => $this->auctionBiddingService->calculateMinimumBid($auctionItem->fresh())
                    ]
                ], 409);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to place bid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List my bids
     * GET /api/v2/auction/customer/my-bids
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->auctionBids()
            ->with([
                'auctionItem.car.carBrand',
                'auctionItem.car.carModel',
                'auctionItem.auctionRoom'
            ])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by auction room if provided
        if ($request->has('auction_room_id')) {
            $query->whereHas('auctionItem', function ($q) use ($request) {
                $q->where('auction_room_id', $request->get('auction_room_id'));
            });
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->get('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->get('end_date'));
        }

        $bids = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => BidResource::collection($bids)
        ]);
    }

    /**
     * Get bid details
     * GET /api/v2/auction/customer/my-bids/{id}
     */
    public function show(Request $request, Bid $bid): JsonResponse
    {
        $user = $request->user();

        // Ensure user owns this bid
        if ($bid->bidder_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bid not found'
            ], 404);
        }

        $bid->load([
            'auctionItem.car.carBrand',
            'auctionItem.car.carModel',
            'auctionItem.car.carCategory',
            'auctionItem.auctionRoom.currency',
            'auctionItem.seller'
        ]);


        return response()->json([
            'success' => true,
            'data' => BidResource::make($bid)
        ]);
    }
}
