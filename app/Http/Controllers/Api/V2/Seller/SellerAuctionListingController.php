<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitListingRequest;
use App\Http\Resources\V2\Seller\AuctionListingRequestResource;
use App\Models\AuctionListingRequest;
use App\Models\Car;
use App\Services\AuctionListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SellerAuctionListingController extends Controller
{
    public function __construct(
        private AuctionListingService $auctionListingService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Submit a new auction listing request
     */
    public function store(SubmitListingRequest $request): JsonResponse
    {
        $car = Car::findOrFail($request->car_id);

        // Verify the seller owns this car
        if ($car->user_id !== Auth::id()) {
            return response()->json([
                'success'=> false,
                'message' => 'You can only submit listing requests for your own cars'
            ], 403);
        }

        $listingRequest = $this->auctionListingService->submitListingRequest(
            $car,
            Auth::user(),
            $request->validated()
        );

        return response()->json([
            'success'   => true,
            'message' => 'Listing request submitted successfully',
            "listing_request" => new AuctionListingRequestResource($listingRequest)
        ], 201);
    }

    /**
     * List seller's auction listing requests
     */
    public function index(Request $request): JsonResponse
    {
        $query = $this->auctionListingService->getSellerRequests(Auth::user());

        // Apply status filter if provided
        if ($request->has('status')) {
            $query = $query->where('status', $request->status);
        }

        $requests = $query->with(['car:id,model_id,brand_id,manufacture_year,main_photo,color_id'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success'   => true,
            "message"   => "Listings retrieved successfully",
            'data' => AuctionListingRequestResource::collection($requests->items()),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total()
            ]
        ]);
    }

    /**
     * Get eligible cars for the auction
     */

    public function getAvailableCars(){
        $availableCars = Car::where('user_id', auth('api')->user()->id)
        ->available()
        ->published()
        ->whereDoesntHave('auctionListingRequests', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
        ->get();

        if($availableCars->count() == 0) {
            return response()->json(
                [
                    'success' => true,
                    'message' => "No cars available!"
                ]
                );
        }
        $data = $availableCars->map(function($car){
            return [
                'id' => $car->id,
                'name'  => $car->car_name,
                'main_photo' => $car->main_photo_url
            ];
        });

        return response()->json([
            'success' => true,
            'data'=> $data,
            'message' => "Cars retrieved successfully"
        ]);
    }

    /**
     * Get specific listing request details
     */
    public function show(int $id): JsonResponse
    {
        $request = AuctionListingRequest::with(['car', 'reviewer:id,name'])
            ->where('seller_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success'=> true,
            'data' => new AuctionListingRequestResource($request)
        ]);
    }

    /**
     * Withdraw a pending listing request
     */
    public function destroy(int $id): JsonResponse
    {
        $request = AuctionListingRequest::where('seller_id', Auth::id())
            ->findOrFail($id);

        if ($request->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be withdrawn'
            ], 422);
        }

        $request->delete();

        return response()->json([
            'success' => true,
            'message' => 'Listing request withdrawn successfully'
        ]);
    }
}
