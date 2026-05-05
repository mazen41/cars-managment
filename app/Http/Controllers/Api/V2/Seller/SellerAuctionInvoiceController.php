<?php

namespace App\Http\Controllers\Api\V2\Seller;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Seller\AuctionInvoiceResource;
use App\Http\Resources\V2\Seller\AuctionItemResource;
use App\Models\AuctionInvoice;
use App\Models\AuctionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use App\Services\AuctionInvoiceService;

class SellerAuctionInvoiceController extends Controller
{
    private  $auctionInvoiceService;
    public function __construct(AuctionInvoiceService $auctionInvoiceService)
    {
        $this->middleware('auth:sanctum');
        $this->auctionInvoiceService = $auctionInvoiceService;
    }

    /**
     * List seller's auction items with status
     */
    public function auctionItems(Request $request): JsonResponse
    {
        $query = AuctionItem::with([
            'car:id,model_id,brand_id,manufacture_year,color_id,main_photo',
            'auctionRoom:id,name,status,scheduled_start_at',
            'currentWinner:id,name'
        ])
            ->where('seller_id', Auth::id());

        // Apply status filter if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Apply auction room filter if provided
        if ($request->has('auction_room_id')) {
            $query->where('auction_room_id', $request->auction_room_id);
        }
        // Filter to only items in scheduled auction rooms
        $query->whereHas('auctionRoom', function ($q) {
            $q->whereIn('status', ['scheduled', 'active']);
        });

        $items = $query->orderBy('created_at', 'desc')
            ->paginate(20);


        return response()->json([
            'success'   => true,
            "message"   => "Items retrieved successfully",
            'data' => AuctionItemResource::collection($items->items()),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total()
            ]
        ]);
    }

    /**
     *  Get Auciton item details
     */

    public function auctionItemDetails(int $id): JsonResponse
    {
        $item = AuctionItem::with([
            'car',
            'auctionRoom:id,name,scheduled_start_at,commission_percentage',
            'bids.bidder:id,name,phone',
            'currentWinner:id,name'
        ])
            ->where('seller_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success'   => true,
            "message"   => "Item retrieved successfully",
            'data' => new AuctionItemResource($item)
        ]);
    }

    /**
     * List seller's payout invoices
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuctionInvoice::with([
            'auctionItem.car:id,model_id,brand_id,manufacture_year,main_photo,color_id',
            'auctionItem.auctionRoom:id,name,scheduled_start_at',
            'payment:id,amount,status,method,transaction_id,created_at,payable_id,payable_type'
        ])
            ->whereHas('auctionItem', function ($q) {
                $q->where('seller_id', Auth::id());
            })
            ->where('invoice_type', 'seller_payout');

        // Apply status filter if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Apply date range filter if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate(20);


        return response()->json([
            'success'   => true,
            "message"   => "Invoices retrieved successfully",
            'data' => AuctionInvoiceResource::collection($invoices->items()),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total()
            ],
            'summary' => [
                'total_invoices' => $invoices->total(),
                'total_amount' => single_price($invoices->sum('amount')),
                'total_commission' => single_price($invoices->sum('commission_amount')),
                'total_net_amount' => single_price($invoices->sum('net_amount')),
                'paid_count' => $invoices->where('status', 'paid')->count(),
                'pending_count' => $invoices->where('status', 'pending')->count()
            ]
        ]);
    }

    /**
     * Get specific invoice details
     */
    public function show(int $id): JsonResponse
    {
        $invoice = AuctionInvoice::with([
            'auctionItem.car',
            'auctionItem.auctionRoom:id,name,commission_percentage',
            'auctionItem.currentWinner:id,name,phone',
            'payment:id,amount,status,method,transaction_id,created_at,payable_id,payable_type'
        ])
            ->whereHas('auctionItem', function ($q) {
                $q->where('seller_id', Auth::id());
            })
            ->where('invoice_type', 'seller_payout')
            ->findOrFail($id);

        return response()->json([
            'success'   => true,
            "message"   => "Invoice retrieved successfully",
            'data' => new AuctionInvoiceResource($invoice)
        ]);
    }

    /**
     * Download PDF version of the invoice
     *
     * @param AuctionInvoice $auctionInvoice
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(AuctionInvoice $auctionInvoice): \Illuminate\Http\Response | JsonResponse
    {
        if($auctionInvoice->user_id !== auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $pdfContent = $this->auctionInvoiceService->generateInvoicePdf($auctionInvoice);
        $filename = $this->auctionInvoiceService->generatePdfFilename($auctionInvoice);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
