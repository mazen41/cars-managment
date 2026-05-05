<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionListingRequest;
use App\Services\AuctionListingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuctionListingRequestController extends Controller
{
    protected AuctionListingService $auctionListingService;

    public function __construct(AuctionListingService $auctionListingService)
    {
        $this->auctionListingService = $auctionListingService;
        $this->middleware('permission:view_auction_listing_requests')->only(['index', 'show', 'stats']);
        $this->middleware('permission:approve_auction_listing_requests')->only(['approve', 'bulkApprove']);
        $this->middleware('permission:reject_auction_listing_requests')->only(['reject', 'bulkReject']);
    }

    /**
     * List pending requests with pagination
     * GET /admin/auction-listing-requests
     */
    public function index(Request $request)
    {
        $query = AuctionListingRequest::with([
            'car.carBrand',
            'car.carModel',
            'car.carColor',
            'seller',
            'reviewer'
        ]);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->whereHas('car', function ($carQuery) use ($request) {
                $carQuery->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('model', 'like', '%' . $request->search . '%');
            })->orWhereHas('seller', function ($sellerQuery) use ($request) {
                $sellerQuery->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // Return JSON for API requests
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data' => $requests
            ]);
        }

        // Get statistics for web view
        $stats = [
            'pending_count' => AuctionListingRequest::where('status', 'pending')->count(),
            'approved_count' => AuctionListingRequest::where('status', 'approved')->count(),
            'rejected_count' => AuctionListingRequest::where('status', 'rejected')->count(),
            'total_count' => AuctionListingRequest::count(),
        ];

        return view('backend.auctions.listing-requests.index', compact('requests', 'stats'));
    }

    /**
     * Get request details
     * GET /admin/auction-listing-requests/{id}
     */
    public function show(Request $httpRequest, AuctionListingRequest $auctionListingRequest)
    {
        $request = $auctionListingRequest->load([
            'car.carBrand',
            'car.carModel',
            'car.carColor',
            'car.carCategory',
            'seller',
            'reviewer'
        ]);

        // Return JSON for API requests
        if ($httpRequest->wantsJson() || $httpRequest->is('api/*')) {
            // Add car details and inspection status if available
            $carDetails = [
                'basic_info' => [
                    'name' => $request->car->name,
                    'brand' => $request->car->carBrand->name ?? 'Unknown',
                    'model' => $request->car->carModel->name ?? 'Unknown',
                    'year' => $request->car->year,
                    'color' => $request->car->carColor->name ?? 'Unknown',
                    'mileage' => $request->car->mileage,
                    'fuel_type' => $request->car->fuel_type,
                    'transmission' => $request->car->transmission_type,
            ],
            'condition' => [
                'overall_condition' => $request->car->condition,
                'description' => $request->car->description,
            ],
            'pricing' => [
                'requested_starting_price' => $request->requested_starting_price,
                'requested_reserve_price' => $request->requested_reserve_price,
                'estimated_value' => $request->car->estimated_value ?? null,
            ],
            'images' => $request->car->uploads->map(function ($upload) {
                return [
                    'id' => $upload->id,
                    'file_name' => $upload->file_name,
                    'file_original_name' => $upload->file_original_name,
                    'file_size' => $upload->file_size,
                    'type' => $upload->type,
                ];
            }),
            'inspection_status' => $request->car->inspection_status ?? 'not_inspected',
            'has_valid_inspection' => $request->car->hasValidInspection() ?? false,
        ];

            return response()->json([
                'success' => true,
                'data' => [
                    'request' => $request,
                    'car_details' => $carDetails
                ]
            ]);
        }

        // Return view for web requests
        return view('backend.auctions.listing-requests.show', compact('request'));
    }

    /**
     * Approve request
     * POST /admin/auction-listing-requests/{id}/approve
     */
    public function approve(Request $request, AuctionListingRequest $auctionListingRequest): JsonResponse
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {
            if ($auctionListingRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be approved'
                ], 422);
            }

            $admin = Auth::user();
            $success = $this->auctionListingService->approveRequest($auctionListingRequest, $admin);

            if ($success) {
                // Update admin notes if provided
                if ($request->filled('admin_notes')) {
                    $auctionListingRequest->update([
                        'admin_notes' => $request->admin_notes
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Auction listing request approved successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject request with reason
     * POST /admin/auction-listing-requests/{id}/reject
     */
    public function reject(Request $request, AuctionListingRequest $auctionListingRequest): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            if ($auctionListingRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending requests can be rejected'
                ], 422);
            }

            $admin = Auth::user();
            $success = $this->auctionListingService->rejectRequest(
                $auctionListingRequest,
                $admin,
                $request->reason
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Auction listing request rejected successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary statistics for admin dashboard
     * GET /admin/auction-listing-requests/stats
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'pending_count' => AuctionListingRequest::where('status', 'pending')->count(),
                'approved_count' => AuctionListingRequest::where('status', 'approved')->count(),
                'rejected_count' => AuctionListingRequest::where('status', 'rejected')->count(),
                'total_count' => AuctionListingRequest::count(),
                'recent_requests' => AuctionListingRequest::with(['car.carBrand', 'seller'])
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($request) {
                        return [
                            'id' => $request->id,
                            'car_name' => $request->car->name ?? 'Unknown Car',
                            'brand' => $request->car->carBrand->name ?? 'Unknown',
                            'seller_name' => $request->seller->name,
                            'requested_starting_price' => $request->requested_starting_price,
                            'created_at' => $request->created_at,
                        ];
                    })
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve multiple requests
     * POST /admin/auction-listing-requests/bulk-approve
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        $request->validate([
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'required|exists:auction_listing_requests,id',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $admin = Auth::user();
            $successCount = 0;
            $errors = [];

            foreach ($request->request_ids as $requestId) {
                $listingRequest = AuctionListingRequest::find($requestId);

                if ($listingRequest && $listingRequest->status === 'pending') {
                    $success = $this->auctionListingService->approveRequest($listingRequest, $admin);

                    if ($success) {
                        if ($request->filled('admin_notes')) {
                            $listingRequest->update(['admin_notes' => $request->admin_notes]);
                        }
                        $successCount++;
                    } else {
                        $errors[] = "Failed to approve request ID: {$requestId}";
                    }
                } else {
                    $errors[] = "Request ID {$requestId} is not pending or not found";
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$successCount} requests",
                'approved_count' => $successCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk approve requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject multiple requests
     * POST /admin/auction-listing-requests/bulk-reject
     */
    public function bulkReject(Request $request): JsonResponse
    {
        $request->validate([
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'required|exists:auction_listing_requests,id',
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $admin = Auth::user();
            $successCount = 0;
            $errors = [];

            foreach ($request->request_ids as $requestId) {
                $listingRequest = AuctionListingRequest::find($requestId);

                if ($listingRequest && $listingRequest->status === 'pending') {
                    $success = $this->auctionListingService->rejectRequest(
                        $listingRequest,
                        $admin,
                        $request->reason
                    );

                    if ($success) {
                        $successCount++;
                    } else {
                        $errors[] = "Failed to reject request ID: {$requestId}";
                    }
                } else {
                    $errors[] = "Request ID {$requestId} is not pending or not found";
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully rejected {$successCount} requests",
                'rejected_count' => $successCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk reject requests: ' . $e->getMessage()
            ], 500);
        }
    }
}
