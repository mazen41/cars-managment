<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Seller\AuctionOfferResource;
use App\Models\AuctionOffer;
use App\Services\AuctionOfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SellerAuctionOfferController extends Controller
{
    public function __construct(
        private AuctionOfferService $auctionOfferService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * List offers on seller's auction items
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuctionOffer::with(['auctionItem.car:id,model_id,brand_id,color_id,manufacture_year,main_photo','buyer:id,name,phone'])
            ->where('seller_id', Auth::id());

        // Apply status filter if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Apply item filter if provided
        if ($request->has('auction_item_id')) {
            $query->where('auction_item_id', $request->auction_item_id);
        }

        $offers = $query->orderBy('created_at', 'desc')
            ->paginate(20);



        return response()->json([
            'success' => true,
            "message"   => "Offers retrieved successfully",
            'data' => AuctionOfferResource::collection($offers->items()),
            'pagination' => [
                'current_page' => $offers->currentPage(),
                'last_page' => $offers->lastPage(),
                'per_page' => $offers->perPage(),
                'total' => $offers->total()
            ]
        ]);
    }

    /**
     * Get specific offer details
     */
    public function show(int $id): JsonResponse
    {
        $offer = AuctionOffer::with([
            'auctionItem',
            'auctionItem.car:id,model_id,brand_id,color_id,manufacture_year,main_photo',
            'auctionItem.auctionRoom:id,name',
            'buyer:id,name,phone'
        ])
            ->where('seller_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            "message"   => "Offer retrieved successfully",
            'data' => new AuctionOfferResource($offer)
        ]);
    }

    /**
     * Accept an offer
     */
    public function accept(Request $request): JsonResponse
    {
        $request->validate([
            'id' => [
                'required',
                'integer',
                Rule::exists('auction_offers', 'id')->where(function ($query) {
                    $query->where('seller_id', Auth::id());
                })
            ],
            'response_message' => 'nullable|string|max:500'
        ]);

        $offer = AuctionOffer::where('seller_id', Auth::id())
            ->findOrFail($request->id);

        if (!$offer->canBeAccepted()) {
            return response()->json([
                'success' => false,
                'message' => 'This offer cannot be accepted. It may have expired or already been responded to.'
            ], 422);
        }

        // Check if auction item is still available for offers
        if ($offer->auctionItem->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This item is no longer available for offers.'
            ], 422);
        }

        $success = $this->auctionOfferService->acceptOffer($offer, Auth::user(), $request->response_message);

        if (!$success) {
            return response()->json([
                'message' => 'Failed to accept offer. Please try again.'
            ], 500);
        }

        return response()->json([
            'message' => 'Offer accepted successfully',
            'data' => [
                'id' => $offer->id,
                'status' => $offer->fresh()->status,
                'responded_at' => $offer->fresh()->responded_at
            ]
        ]);
    }

    /**
     * Reject an offer
     */
    public function reject(Request $request): JsonResponse
    {
        $request->validate([
            'id' => [
                'required',
                'integer',
                Rule::exists('auction_offers', 'id')->where(function ($query) {
                    $query->where('seller_id', Auth::id());
                })
            ],
            'reason' => 'required|string|max:500'
        ]);

        $offer = AuctionOffer::where('seller_id', Auth::id())
            ->findOrFail($request->id);

        if ($offer->status !== 'pending') {
            return response()->json([
                'message' => 'This offer has already been responded to.'
            ], 422);
        }

        $success = $this->auctionOfferService->rejectOffer(
            $offer,
            Auth::user(),
            $request->reason
        );

        if (!$success) {
            return response()->json([
                'message' => 'Failed to reject offer. Please try again.'
            ], 500);
        }

        return response()->json([
            'success'=> true,
            'message' => 'Offer rejected successfully',
            'data' => [
                'id' => $offer->id,
                'status' => $offer->fresh()->status,
                'seller_response' => $offer->fresh()->seller_response,
                'responded_at' => $offer->fresh()->responded_at
            ]
        ]);
    }
}
