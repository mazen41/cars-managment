<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitOfferRequest;
use App\Http\Resources\V2\Auction\OfferResource;
use App\Models\AuctionItem;
use App\Models\AuctionOffer;
use App\Services\AuctionOfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuctionOfferController extends Controller
{
    public function __construct(
        private AuctionOfferService $auctionOfferService
    ) {}

    /**
     * Submit offer
     * POST /api/v2/auction-items/{id}/offers
     */
    public function store(AuctionItem $auctionItem, SubmitOfferRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $amount = $request->validated('amount');
            $message = $request->validated('message');

            // Check if user can submit offers ( deposit required)
            if (!$user->canBid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insurance deposit required',
                    'data' => [
                        'has_deposit' => $user->hasInsuranceDeposit()
                    ]
                ], 403);
            }

            // Check if item can receive offers
            if (!$auctionItem->canReceiveOffers()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offers cannot be submitted after auction starts',
                    'data' => [
                        'item_status' => $auctionItem->status,
                        'auction_started' => $auctionItem->auctionRoom->status === 'active'
                    ]
                ], 409);
            }

            // Validate offer amount
            if ($amount < $auctionItem->starting_price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer must be at least the starting price',
                    'data' => [
                        'starting_price' => $auctionItem->starting_price,
                        'submitted_amount' => $amount
                    ]
                ], 422);
            }

            // Check if user is trying to make offer on their own item
            if ($auctionItem->seller_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot make offers on your own items'
                ], 403);
            }

            // Create the offer
            $offer = $this->auctionOfferService->createOffer($auctionItem, $user, $amount, $message);

            return response()->json([
                'success' => true,
                'message' => 'Offer submitted successfully',
                'data' => OfferResource::make($offer)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit offer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List my offers
     * GET /api/v2/auction/customer/my-offers
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->auctionOffers()
            ->with([
                'auctionItem.car.carBrand',
                'auctionItem.car.carModel',
                'auctionItem.auctionRoom',
                'seller'
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

        $offers = $query->paginate($request->get('per_page', 15));

        // Add computed fields
        $offers->getCollection()->transform(function ($offer) {
            $offer->is_pending = $offer->isPending();
            $offer->can_be_withdrawn = $offer->canBeWithdrawn();
            $offer->item_status = $offer->auctionItem->status;
            $offer->auction_status = $offer->auctionItem->auctionRoom->status;

            return $offer;
        });

        return response()->json([
            'success' => true,
            'data' => OfferResource::collection($offers)
        ]);
    }

    /**
     * Get offer details
     * GET /api/v2/auction/customer/my-offers/{id}
     */
    public function show(Request $request, AuctionOffer $auctionOffer): JsonResponse
    {
        $user = $request->user();

        // Ensure user owns this offer
        if ($auctionOffer->buyer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found'
            ], 404);
        }

        $auctionOffer->load([
            'auctionItem.car.carBrand',
            'auctionItem.car.carModel',
            'auctionItem.car.carCategory',
            'auctionItem.auctionRoom.currency',
            'seller'
        ]);



        return response()->json([
            'success' => true,
            'data' => OfferResource::make($auctionOffer)
        ]);
    }

    /**
     * Withdraw offer
     * DELETE /api/v2/auction/customer/my-offers/{id}
     */
    public function destroy(Request $request, AuctionOffer $auctionOffer): JsonResponse
    {
        try {
            $user = $request->user();

            // Ensure user owns this offer
            if ($auctionOffer->buyer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found'
                ], 404);
            }

            // Check if offer can be withdrawn
            if (!$auctionOffer->canBeWithdrawn()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This offer cannot be withdrawn',
                    'data' => [
                        'offer_status' => $auctionOffer->status,
                        'reason' => $auctionOffer->status === 'accepted' ? 'Offer has been accepted' :
                                   ($auctionOffer->status === 'rejected' ? 'Offer has been rejected' : 'Offer has expired')
                    ]
                ], 409);
            }

            $success = $this->auctionOfferService->withdrawOffer($auctionOffer, $user);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to withdraw offer'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Offer withdrawn successfully',
                'data' => $auctionOffer->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to withdraw offer: ' . $e->getMessage()
            ], 500);
        }
    }
}
