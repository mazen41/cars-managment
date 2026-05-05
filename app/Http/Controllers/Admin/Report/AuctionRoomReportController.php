<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\AuctionRoom;
use App\Services\AuctionRoomReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;

class AuctionRoomReportController extends Controller
{
    protected AuctionRoomReportService $reportService;

    public function __construct(AuctionRoomReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware('permission:view_auction_reports')->only(['show', 'exportPdf']);
    }

    /**
     * Display comprehensive report for a completed auction room
     * GET /admin/auction-rooms/{auctionRoom}/report
     *
     * @param AuctionRoom $auctionRoom
     * @return View|\Illuminate\Http\RedirectResponse
     */
    public function show(AuctionRoom $auctionRoom)
    {
        // Validate room is completed before showing report
        if ($auctionRoom->status !== 'completed') {
            flash(translate('Reports are only available for completed auction rooms'))->error();
            return redirect()->route('admin.auction-rooms.show', $auctionRoom->id);
        }

        // Get comprehensive report data
        $reportData = $this->reportService->getReportData($auctionRoom);

        // Pass report data to view
        return view('backend.auctions.reports.show', $reportData);
    }


    /**
     * Get filtered bids for an auction room
     * GET /admin/auction-rooms/{auctionRoom}/report/bids
     *
     * @param Request $request
     * @param AuctionRoom $auctionRoom
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilteredBids(Request $request, AuctionRoom $auctionRoom)
    {
        // Validate room is completed
        if ($auctionRoom->status !== 'completed') {
            return response()->json(['error' => 'Reports are only available for completed auction rooms'], 422);
        }

        // Get report data
        $reportData = $this->reportService->getReportData($auctionRoom);
        $bids = $reportData['bids'];

        // Apply filters
        $filters = $this->buildBidFilters($request);
        $filteredBids = $this->reportService->applyFilters($bids, $filters, 'bids');

        // Return filtered data with count
        return response()->json([
            'success' => true,
            'data' => $filteredBids->values(),
            'count' => $filteredBids->count(),
            'total' => $bids->count(),
        ]);
    }

    /**
     * Get filtered offers for an auction room
     * GET /admin/auction-rooms/{auctionRoom}/report/offers
     *
     * @param Request $request
     * @param AuctionRoom $auctionRoom
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilteredOffers(Request $request, AuctionRoom $auctionRoom)
    {
        // Validate room is completed
        if ($auctionRoom->status !== 'completed') {
            return response()->json(['error' => 'Reports are only available for completed auction rooms'], 422);
        }

        // Get report data
        $reportData = $this->reportService->getReportData($auctionRoom);
        $offers = $reportData['offers'];

        // Apply filters
        $filters = $this->buildOfferFilters($request);
        $filteredOffers = $this->reportService->applyFilters($offers, $filters, 'offers');

        // Return filtered data with count
        return response()->json([
            'success' => true,
            'data' => $filteredOffers->values(),
            'count' => $filteredOffers->count(),
            'total' => $offers->count(),
        ]);
    }

    /**
     * Get filtered audit logs for an auction room
     * GET /admin/auction-rooms/{auctionRoom}/report/audit-logs
     *
     * @param Request $request
     * @param AuctionRoom $auctionRoom
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilteredAuditLogs(Request $request, AuctionRoom $auctionRoom)
    {
        // Validate room is completed
        if ($auctionRoom->status !== 'completed') {
            return response()->json(['error' => 'Reports are only available for completed auction rooms'], 422);
        }

        // Get report data
        $reportData = $this->reportService->getReportData($auctionRoom);
        $auditLogs = $reportData['audit_log'];

        // Apply filters
        $filters = $this->buildAuditLogFilters($request);
        $filteredLogs = $this->reportService->applyFilters($auditLogs, $filters, 'audit_logs');

        // Return filtered data with count
        return response()->json([
            'success' => true,
            'data' => $filteredLogs->values(),
            'count' => $filteredLogs->count(),
            'total' => $auditLogs->count(),
        ]);
    }

    /**
     * Get filtered items for an auction room
     * GET /admin/auction-rooms/{auctionRoom}/report/items
     *
     * @param Request $request
     * @param AuctionRoom $auctionRoom
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFilteredItems(Request $request, AuctionRoom $auctionRoom)
    {
        // Validate room is completed
        if ($auctionRoom->status !== 'completed') {
            return response()->json(['error' => 'Reports are only available for completed auction rooms'], 422);
        }

        // Get report data
        $reportData = $this->reportService->getReportData($auctionRoom);
        $items = $reportData['items'];

        // Apply filters
        $filters = $this->buildItemFilters($request);
        $filteredItems = $this->reportService->applyFilters($items, $filters, 'items');

        // Return filtered data with count
        return response()->json([
            'success' => true,
            'data' => $filteredItems->values(),
            'count' => $filteredItems->count(),
            'total' => $items->count(),
        ]);
    }

    /**
     * Build bid filters from request
     *
     * @param Request $request
     * @return array
     */
    protected function buildBidFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('status')) {
            $filters['status'] = $request->input('status');
        }

        if ($request->filled('bidder_name')) {
            // Note: Name filtering will be handled in the service layer
            $filters['bidder_name'] = $request->input('bidder_name');
        }

        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->input('date_from');
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->input('date_to');
        }

        return $filters;
    }

    /**
     * Build offer filters from request
     *
     * @param Request $request
     * @return array
     */
    protected function buildOfferFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('status')) {
            $filters['status'] = $request->input('status');
        }

        if ($request->filled('buyer_name')) {
            $filters['buyer_name'] = $request->input('buyer_name');
        }

        if ($request->filled('seller_name')) {
            $filters['seller_name'] = $request->input('seller_name');
        }

        return $filters;
    }

    /**
     * Build audit log filters from request
     *
     * @param Request $request
     * @return array
     */
    protected function buildAuditLogFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('action')) {
            $filters['action'] = $request->input('action');
        }

        if ($request->filled('user_name')) {
            $filters['user_name'] = $request->input('user_name');
        }

        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->input('date_from');
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->input('date_to');
        }

        return $filters;
    }

    /**
     * Build item filters from request
     *
     * @param Request $request
     * @return array
     */
    protected function buildItemFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('status')) {
            $filters['status'] = $request->input('status');
        }

        if ($request->filled('min_price')) {
            $filters['min_price'] = $request->input('min_price');
        }

        if ($request->filled('max_price')) {
            $filters['max_price'] = $request->input('max_price');
        }

        return $filters;
    }
}
