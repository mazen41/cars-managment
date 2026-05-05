<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuctionInvoice;
use App\Services\AuctionInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\V2\Auction\AuctionInvoiceResource;

class AuctionInvoiceController extends Controller
{
    public function __construct(
        private AuctionInvoiceService $auctionInvoiceService
    ) {}

    /**
     * List my invoices
     * GET /api/v2/auction/customer/auction-invoices
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->auctionInvoices()
            ->where('invoice_type', 'buyer_payment')
            ->with([
                'auctionItem.car.carBrand',
                'auctionItem.car.carModel',
                'auctionItem.auctionRoom.currency',
                'payment'
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

        // Filter by paid/unpaid
        if ($request->has('paid')) {
            $isPaid = filter_var($request->get('paid'), FILTER_VALIDATE_BOOLEAN);
            if ($isPaid) {
                $query->where('status', 'paid');
            } else {
                $query->where('status', 'pending');
            }
        }

        $invoices = $query->paginate($request->get('per_page', 15));

        // Add computed fields
        $invoices->getCollection()->transform(function ($invoice) {
            $invoice->is_paid = $invoice->isPaid();
            $invoice->is_buyer_invoice = $invoice->isBuyerInvoice();
            $invoice->days_overdue = $invoice->due_date && $invoice->due_date->isPast() ?
                                    $invoice->due_date->diffInDays(now()) : 0;
            $invoice->is_overdue = $invoice->due_date && $invoice->due_date->isPast() && !$invoice->isPaid();

            return $invoice;
        });

        return response()->json([
            'success' => true,
            'data' => AuctionInvoiceResource::collection($invoices),
            "pagination" => [
                    "current_page" => $invoices->currentPage(),
                    "last_page" => $invoices->lastPage(),
                    "per_page" => $invoices->perPage(),
                    "total" => $invoices->total(),
                    "from" => $invoices->firstItem(),
                    "to" => $invoices->lastItem(),
                ],
        ]);
    }

    /**
     * Get invoice details
     * GET /api/v2/auction/customer/auction-invoices/{id}
     */
    public function show(Request $request, AuctionInvoice $auctionInvoice): JsonResponse
    {
        $user = $request->user();

        // Ensure user owns this invoice and it's a auction/customer invoice
        if ($auctionInvoice->user_id !== $user->id || $auctionInvoice->invoice_type !== 'buyer_payment') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $auctionInvoice->load([
            'auctionItem.car.carBrand',
            'auctionItem.car.carModel',
            'auctionItem.car.carCategory',
            'auctionItem.auctionRoom.currency',
            'auctionItem.seller',
            'payment'
        ]);



        return response()->json([
            'success' => true,
            'data' => AuctionInvoiceResource::make($auctionInvoice)
        ]);
    }

    /**
     * Pay invoice using existing payment system
     * POST /api/v2/auction/customer/auction-invoices/{id}/pay
     */
    public function pay(Request $request, AuctionInvoice $auctionInvoice): JsonResponse
    {
        try {
            $user = $request->user();

            // Ensure user owns this invoice and it's a buyer_payment
            if ($auctionInvoice->user_id !== $user->id || $auctionInvoice->invoice_type !== 'buyer_payment') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            // Check if invoice is already paid
            if ($auctionInvoice->isPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice is already paid',
                    'data' => $auctionInvoice->load('payment')
                ], 409);
            }

            // Validate payment provider
            $request->validate([
                'provider' => ['required', 'string'],
                'code' => ['sometimes', 'string'],
                'metadata' => ['sometimes', 'array']
            ]);

            // Prepare payment request data for the existing payment system
            $paymentRequestData = [
                'payment_type' => \App\Enums\PaymentType::AUCTION_INVOICE_PAYMENT,
                'provider' => $request->get('provider'),
                'payment_type_id' => $auctionInvoice->id,
                'purchase_code' => $request->get('code', ''),
                'metadata' => $request->get('metadata', [])
            ];

            // Merge with request data
            $request->merge($paymentRequestData);

            // Create payment request DTO
            $paymentRequest = \App\DTOs\Payment\PaymentRequest::fromRequest($request);

            // Process payment through existing payment service
            $paymentService = app(\App\Services\Payment\PaymentService::class);
            $result = $paymentService->processPayment($paymentRequest);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'invoice' => $auctionInvoice->fresh()->load('payment', 'auctionItem.car')
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Auction invoice payment failed', [
                'invoice_id' => $auctionInvoice->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment summary for user
     * GET /api/v2/auction/customer/auction-invoices/summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $unpaidInvoices = $this->auctionInvoiceService->getUserUnpaidInvoices($user);

        $summary = [
            'total_unpaid_amount' => $unpaidInvoices->sum('amount'),
            'unpaid_count' => $unpaidInvoices->count(),
            'overdue_count' => $unpaidInvoices->filter(function ($invoice) {
                return $invoice->due_date && $invoice->due_date->isPast();
            })->count(),
            'total_paid_amount' => $user->auctionInvoices()
                ->where('invoice_type', 'auction/customer_payment')
                ->where('status', 'paid')
                ->sum('amount'),
            'total_invoices' => $user->auctionInvoices()
                ->where('invoice_type', 'auction/customer_payment')
                ->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Download PDF version of the invoice
     *
     * @param AuctionInvoice $auctionInvoice
     * @return Response | JsonResponse
     */
    public function downloadPdf(AuctionInvoice $auctionInvoice): Response | JsonResponse
    {
        if($auctionInvoice->user_id !== auth()->id()) {
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
