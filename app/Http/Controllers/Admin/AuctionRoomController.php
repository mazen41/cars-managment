<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAuctionRoomRequest;
use App\Models\AuctionRoom;
use App\Models\AuctionItem;
use App\Models\Car;
use App\Models\Currency;
use App\Services\AuctionRoomService;
use App\Jobs\StartAuctionRoomJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuctionRoomController extends Controller
{
    protected AuctionRoomService $auctionRoomService;

    public function __construct(AuctionRoomService $auctionRoomService)
    {
        $this->auctionRoomService = $auctionRoomService;
        $this->middleware('permission:view_auction_rooms')->only(['index', 'show', 'liveStats', 'monitor']);
        $this->middleware('permission:create_auction_room')->only(['create', 'store']);
        $this->middleware('permission:edit_auction_room')->only(['edit', 'update']);
        $this->middleware('permission:start_auction_room')->only(['start', 'setScheduled']);
        $this->middleware('permission:cancel_auction_room')->only(['cancel']);
        $this->middleware('permission:manage_auction_items')->only(['addItem', 'removeItem', 'reorderItems']);
        $this->middleware('permission:monitor_auction_room')->only(['monitor', 'liveStats']);
    }

    /**
     * List auction rooms with filters
     * GET /admin/auction-rooms
     */
    public function index(Request $request)
    {
        $query = AuctionRoom::with(['currency', 'creator', 'auctionItems'])
            ->withCount('auctionItems');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $rooms = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Return view for web requests
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $rooms
            ]);
        }

        return view('backend.auctions.rooms.index', compact('rooms'));
    }

    /**
     * Show create form
     * GET /admin/auction-rooms/create
     */
    public function create()
    {
        $currencies = Currency::where('status', 1)->get();
        return view('backend.auctions.rooms.create', compact('currencies'));
    }

    /**
     * Show edit form
     * GET /admin/auction-rooms/{id}/edit
     */
    public function edit(AuctionRoom $auctionRoom)
    {
        if ($auctionRoom->status !== 'draft') {
            flash(translate('Only draft rooms can be edited'))->error();
            return redirect()->route('admin.auction-rooms.show', $auctionRoom->id);
        }

        $currencies = Currency::where('status', 1)->get();
        $room = $auctionRoom;
        return view('backend.auctions.rooms.edit', compact('room', 'currencies'));
    }

    /**
     * Create auction room
     * POST /admin/auction-rooms
     */
    public function store(CreateAuctionRoomRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = Auth::id();

            $room = $this->auctionRoomService->createRoom($data);

            // Return JSON for API requests
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auction room created successfully',
                    'data' => $room->load(['currency', 'creator'])
                ], 201);
            }

            flash(translate('Auction room created successfully'))->success();
            return redirect()->route('admin.auction-rooms.show', $room->id);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create auction room: ' . $e->getMessage()
                ], 500);
            }

            flash(translate('Failed to create auction room: ') . $e->getMessage())->error();
            return back()->withInput();
        }
    }

    /**
     * Get room details
     * GET /admin/auction-rooms/{id}
     */
    public function show(Request $request, AuctionRoom $auctionRoom)
    {
        $room = $auctionRoom->load([
            'currency',
            'creator',
            'auctionItems.car.carBrand',
            'auctionItems.car.carModel',
            'auctionItems.seller',
            'auctionItems.currentWinner'
        ]);

        $statistics = $this->auctionRoomService->getRoomStatistics($auctionRoom);

        // Return JSON for API requests
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'room' => $room,
                    'statistics' => $statistics
                ]
            ]);
        }

        // Get available cars for adding to auction
        $availableCars = Car::published()
            ->available()
            // only cars with approved auction listing requests
             ->whereHas('auctionListingRequests', function ($query)  {
                $query->approved();
            })
            //product by admin or staff only
            ->orWhereHas('user', function ($query) {
                $query->where('user_type', 'admin')->orWhere('user_type', 'staff');
            })
            //exclude cars already in active or scheduled auctions
            ->whereDoesntHave('auctionItems', function ($query) {
                $query->whereHas('auctionRoom', function ($q) {
                    $q->whereIn('status', ['scheduled', 'active']);
                });
            })
            ->get();

        return view('backend.auctions.rooms.show', compact('room', 'statistics', 'availableCars'));
    }

    /**
     * Update room configuration
     * PUT /admin/auction-rooms/{id}
     */
    public function update(CreateAuctionRoomRequest $request, AuctionRoom $auctionRoom)
    {

        try {
            if ($auctionRoom->status !== 'draft') {
                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only draft rooms can be updated'
                    ], 422);
                }

                flash(translate('Only draft rooms can be updated'))->error();
                return back();
            }

            $room = $this->auctionRoomService->updateRoom($auctionRoom, $request->validated());

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auction room updated successfully',
                    'data' => $room->load(['currency', 'creator'])
                ]);
            }

            flash(translate('Auction room updated successfully'))->success();
            return redirect()->route('admin.auction-rooms.show', $room->id);
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update auction room: ' . $e->getMessage()
                ], 500);
            }

            flash(translate('Failed to update auction room: ') . $e->getMessage())->error();
            return back()->withInput();
        }
    }

    /**
     * Start auction room manually
     * POST /admin/auction-rooms/{id}/start
     */
    public function start(AuctionRoom $auctionRoom): JsonResponse
    {
        try {
            if (!$auctionRoom->canStart()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room cannot be started. Check status and items.'
                ], 422);
            }

            $success = $this->auctionRoomService->startRoom($auctionRoom);

            if ($success) {
                // Dispatch the job to actually start the room
                StartAuctionRoomJob::dispatch($auctionRoom->id, true);

                return response()->json([
                    'success' => true,
                    'message' => 'Auction room started successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to start auction room'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start auction room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function setScheduled(Request $request, AuctionRoom $auctionRoom): JsonResponse
    {
        try {
            if (!$auctionRoom->canSchedul()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room cannot be scheduled. Check status and items.'
                ], 422);
            }

            $success = $this->auctionRoomService->setRoomScheduled($auctionRoom);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auction room scheduled successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule auction room'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule auction room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel auction room
     * POST /admin/auction-rooms/{id}/cancel
     */
    public function cancel(Request $request, AuctionRoom $auctionRoom): JsonResponse
    {

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            if ($auctionRoom->status === 'completed' || $auctionRoom->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is already completed or cancelled'
                ], 422);
            }

            $success = $this->auctionRoomService->cancelRoom($auctionRoom, $request->reason);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auction room cancelled successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel auction room'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel auction room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add item to room
     * POST /admin/auction-rooms/{id}/items
     */
    public function addItem(Request $request, AuctionRoom $auctionRoom): JsonResponse
    {

        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'starting_price' => 'required|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'sequence_order' => 'nullable|integer|min:1'
        ]);

        try {
            if ($auctionRoom->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Items can only be added to draft rooms'
                ], 422);
            }

            $car = Car::findOrFail($request->car_id);

            // Check if car is already in another active auction
            $existingItem = AuctionItem::whereHas('auctionRoom', function ($query) {
                $query->whereIn('status', ['scheduled', 'active']);
            })->where('car_id', $car->id)->first();

            if ($existingItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'This car is already in another active auction'
                ], 422);
            }

            $item = $this->auctionRoomService->addItemToRoom($auctionRoom, $car, [
                'starting_price' => $request->starting_price,
                'reserve_price' => $request->reserve_price,
                'sequence_order' => $request->sequence_order
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item added to auction room successfully',
                'data' => $item->load(['car.carBrand', 'car.carModel', 'seller'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item to room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from room
     * DELETE /admin/auction-rooms/{id}/items/{itemId}
     */
    public function removeItem(AuctionRoom $auctionRoom, int $itemId): JsonResponse
    {

        try {
            $item = AuctionItem::where('auction_room_id', $auctionRoom->id)
                ->where('id', $itemId)
                ->firstOrFail();

            if ($item->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending items can be removed'
                ], 422);
            }

            $success = $this->auctionRoomService->removeItemFromRoom($item);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from auction room successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from room'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from room: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder items
     * PUT /admin/auction-rooms/{id}/items/reorder
     */
    public function reorderItems(Request $request, AuctionRoom $auctionRoom): JsonResponse
    {

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:auction_items,id',
            'items.*.sequence_order' => 'required|integer|min:1'
        ]);

        try {
            if ($auctionRoom->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Items can only be reordered in draft rooms'
                ], 422);
            }

            DB::transaction(function () use ($request, $auctionRoom) {
                foreach ($request->items as $itemData) {
                    AuctionItem::where('id', $itemData['id'])
                        ->where('auction_room_id', $auctionRoom->id)
                        ->update(['sequence_order' => $itemData['sequence_order']]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Items reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Real-time room statistics
     * GET /admin/auction-rooms/{id}/live-stats
     */
    public function liveStats(AuctionRoom $auctionRoom): JsonResponse
    {
        try {
            $statistics = $this->auctionRoomService->getRoomStatistics($auctionRoom);

            // Add real-time data
            $currentItem = $auctionRoom->getCurrentItem();
            $liveStats = [
                'room_status' => $auctionRoom->status,
                'current_item' => $currentItem ? [
                    'id' => $currentItem->id,
                    'car_name' => $currentItem->car->name ?? 'Unknown Car',
                    'current_price' => $currentItem->current_price,
                    'total_bids' => $currentItem->total_bids,
                    'ends_at' => $currentItem->ends_at,
                    'seconds_remaining' => $currentItem->getSecondsRemaining()
                ] : null,
                'total_viewers' => 0, // This would come from WebSocket connection tracking
                'active_bidders' => 0, // This would come from recent bid activity
            ];

            return response()->json([
                'success' => true,
                'data' => array_merge($statistics, $liveStats)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get live statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Live auction monitor view
     * GET /admin/auction-rooms/{id}/monitor
     */
    public function monitor(AuctionRoom $auctionRoom)
    {
        if ($auctionRoom->status !== 'active') {
            flash(translate('Only active rooms can be monitored'))->error();
            return redirect()->route('admin.auction-rooms.show', $auctionRoom->id);
        }

        $room = $auctionRoom->load(
            ['creator',
            'auctionItems.car.carBrand',
            'auctionItems.car.carModel',
            'auctionItems.currentWinner',
            'auctionItems.seller',
            'auctionItems.bids'
            ]);
        $currentItem = $auctionRoom->getCurrentItem()->load([
            'car.carBrand',
            'car.carModel',
            'currentWinner',
            'bids' => function ($query) {
                $query->latest();
            },
            'bids.bidder'
        ]);

        if ($currentItem) {
            $currentItem->load(['car.carBrand', 'car.carModel', 'currentWinner']);
        }

        return view('backend.auctions.rooms.monitor', compact('room', 'currentItem'));
    }

    /**
     * Get Auction Item starting price
     * GET /admin/auction-item-starting-price
     */

    public function getAuctionItemStartingPrice(): JsonResponse
{
    $car_id = request()->integer('car_id'); // Use 'integer' for safer input
    $car = Car::find($car_id);

    if (!$car) {
        return response()->json([
            'success' => false,
            'message' => 'Car not found'
        ], 404);
    }

    // Check if the specific car instance has an approved auction listing request
    if ($car->hasApprovedAuctionListingRequest()) {

        $starting_price = $car->auctionListingRequests()
                             ->approved()
                             ->latest()
                             ->value('requested_starting_price');


        if ($starting_price === null) {
             $starting_price = $car->price;
        }

    } else {
        $starting_price = $car->price;
    }

    return response()->json([
        'success' => true,
        'starting_price' => $starting_price
    ]);
}
}
