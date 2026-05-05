<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Auction\AuctionRoomListResource;
use App\Http\Resources\V2\Auction\AuctionRoomResource;
use App\Models\AuctionRoom;
use App\Models\AuctionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\V2\Auction\AuctionItemListResource;
use App\Http\Resources\V2\CarListResource;
use App\Models\Car;

class AuctionBrowsingController extends Controller
{
    /**
     * List active rooms
     * GET /api/v2/auction/auction-rooms/active
     */
    public function activeRooms(Request $request): JsonResponse
    {
        $query = AuctionRoom::with(['currency', 'createdBy'])
            ->where('status', 'active')
            ->orderBy('scheduled_start_at');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }


        $rooms = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            "message" => "Active auction rooms retrieved successfully",
            'data' => AuctionRoomListResource::collection($rooms),
            "pagination" => [
                    "current_page" => $rooms->currentPage(),
                    "last_page" => $rooms->lastPage(),
                    "per_page" => $rooms->perPage(),
                    "total" => $rooms->total(),
                    "from" => $rooms->firstItem(),
                    "to" => $rooms->lastItem(),
                ],
        ]);
        }

    /**
     * Get Scheduled Auction Rooms
     * GET /api/v2/auction/auction-rooms/scheduled
     */
    public function scheduledRooms(Request $request): JsonResponse
    {
        $query = AuctionRoom::with(['currency', 'createdBy'])
            ->where('status', 'scheduled')
            ->orderBy('scheduled_start_at');
            // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->where('scheduled_start_at', '>=', $request->get('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('scheduled_start_at', '<=', $request->get('end_date'));
        }

        $rooms = $query->paginate($request->get('per_page', 15));

       return response()->json([
            'success' => true,
            "message" => "Scheduled auction rooms retrieved successfully",
            'data' => AuctionRoomListResource::collection($rooms),
            "pagination" => [
                    "current_page" => $rooms->currentPage(),
                    "last_page" => $rooms->lastPage(),
                    "per_page" => $rooms->perPage(),
                    "total" => $rooms->total(),
                    "from" => $rooms->firstItem(),
                    "to" => $rooms->lastItem(),
                ],
        ]);
    }

    /**
     * Get room details
     * GET /api/v2/auction/auction-rooms/{id}
     */
    public function show(AuctionRoom $auctionRoom): JsonResponse
    {
        if($auctionRoom->status !== 'active' && $auctionRoom->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Auction room not found or not accessible.',
            ], 404);
        }
        $auctionRoom->load([
            'currency',
            'createdBy',
            'auctionItems' => function ($query) {
                $query->with(['car', 'seller', 'currentWinner'])
                    ->orderBy('sequence_order');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => new AuctionRoomResource($auctionRoom)
        ]);
    }

    /**
     * List items in room
     * GET /api/v2/auction/auction-rooms/{id}/items
     */
    public function items(AuctionRoom $auctionRoom, Request $request): JsonResponse
    {
         if($auctionRoom->status !== 'active' && $auctionRoom->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Auction room not found or not accessible.',
            ], 404);
        }

        $query = $auctionRoom->auctionItems()
            ->with(['car.carBrand', 'car.carModel', 'seller', 'currentWinner'])
            ->orderBy('sequence_order');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $items = $query->paginate($request->get('per_page', 15));



        return response()->json([
            'success' => true,
            'data' => AuctionItemListResource::collection($items),
        ]);
    }

    /**
     * Get item details
     * GET /api/v2/auction/auction-items/{id}
     */
    public function itemDetails(AuctionItem $auctionItem): JsonResponse
    {
        $auctionItem->load([
            'auctionRoom.currency',
            'car.carBrand',
            'car.carModel',
            'car.carCategory',
            'car.carColor',
            'seller'
        ]);



        return response()->json([
            'success' => true,
            'data' => new AuctionItemListResource($auctionItem)
        ]);
    }

    /**
     * Get bid history (anonymized)
     * GET /api/v2/auction/auction-items/{id}/bids
     */
    public function bidHistory(AuctionItem $auctionItem, Request $request): JsonResponse
    {
        $query = $auctionItem->bids()
            ->with('bidder')
            ->where('status', 'accepted')
            ->orderBy('created_at', 'desc');

        $bids = $query->paginate($request->get('per_page', 20));

        // Anonymize bidder information
        $bids->getCollection()->transform(function ($bid) {
            $bid->bidder_name = 'Bidder ' . substr(md5($bid->bidder->id), 0, 6);
            $bid->is_current_winner = $bid->auction_item_id === $bid->auctionItem->id &&
                                     $bid->bidder_id === $bid->auctionItem->current_winner_id;

            // Remove sensitive bidder information
            unset($bid->bidder);
            unset($bid->ip_address);
            unset($bid->user_agent);
            unset($bid->bid_token);

            return $bid;
        });

        return response()->json([
            'success' => true,
            'data' => $bids
        ]);
    }

     /**
     * Get Auction Cars for browsing
     * GET /api/v2/auction/auction-rooms/cars
     */
    public function auctionCars(Request $request): JsonResponse
    {
        $query = Car::with([
            "auctionItems" => function ($q) {
                $q->whereIn('status', ['active', 'pending'])
                  ->whereHas('auctionRoom', function ($q2) {
                      $q2->whereIn('status', ['active', 'scheduled']);
                  });
            },
            "brand:id,name,logo",
            "model:id,name,brand_id",
            "category:id,name,image",
            "color:id,name,hex_code",
            "features:id,name,image",
            "country:id,name",
            "state:id,name",
            "city:id,name",
        ])
            ->whereHas('auctionItems', function ($q) {
                $q->whereIn('status', ['active', 'pending'])
                  ->whereHas('auctionRoom', function ($q2) {
                      $q2->whereIn('status', ['active', 'scheduled']);
                  });
            });

        $auctionCars = $query->paginate($request->get('per_page', 15));
        return response()->json([
            "result" => true,
            "data" => [
                "cars" => CarListResource::collection($auctionCars),
                "pagination" => [
                    "current_page" => $auctionCars->currentPage(),
                    "last_page" => $auctionCars->lastPage(),
                    "per_page" => $auctionCars->perPage(),
                    "total" => $auctionCars->total(),
                    "from" => $auctionCars->firstItem(),
                    "to" => $auctionCars->lastItem(),
                ],
            ],
            "message" => "Auction Cars retrieved successfully",
        ]);
    }
}
