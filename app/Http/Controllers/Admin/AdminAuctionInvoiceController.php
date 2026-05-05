<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuctionInvoice;
use App\Services\AdminAuctionInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminAuctionInvoiceController extends Controller
{
    protected AdminAuctionInvoiceService $adminAuctionInvoiceService;

    public function __construct(AdminAuctionInvoiceService $adminAuctionInvoiceService)
    {
        $this->adminAuctionInvoiceService = $adminAuctionInvoiceService;

        // Apply middleware for authentication and authorization
        $this->middleware(['auth', 'admin']);

        // Apply permission-based middleware for specific actions
        $this->middleware('permission:view_auction_invoices')->only(['index', 'show', 'analytics']);
        $this->middleware('permission:manage_auction_invoices')->only(['updateStatus']);
        $this->middleware('permission:download_auction_invoices')->only(['downloadPdf']);
        $this->middleware('permission:export_auction_invoices')->only(['export']);
    }

    /**
     * Display a listing of auction invoices with filtering and pagination
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $invoices = $this->adminAuctionInvoiceService->getFilteredInvoices([
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'user_search' => $request->get('user_search'),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_direction' => $request->get('sort_direction', 'desc'),
        ]);

        return view('backend.auctions.invoices.index', compact('invoices'));
    }

    /**
     * Display the specified auction invoice
     *
     * @param AuctionInvoice $auctionInvoice
     * @return View
     */
    public function show(AuctionInvoice $auctionInvoice): View
    {
        $auctionInvoice->load([
            'user',
            'auctionItem.car',
            'auctionItem.auctionRoom',
            'auctionItem.bids' => function ($query) {
                $query->orderBy('amount', 'desc')->limit(5);
            },
            'payment'
        ]);

        return view('backend.auctions.invoices.show', compact('auctionInvoice'));
    }

    /**
     * Download PDF version of the invoice
     *
     * @param AuctionInvoice $auctionInvoice
     * @return Response
     */
    public function downloadPdf(AuctionInvoice $auctionInvoice): Response
    {
        $pdfContent = $this->adminAuctionInvoiceService->generateInvoicePdf($auctionInvoice);
        $filename = $this->adminAuctionInvoiceService->generatePdfFilename($auctionInvoice);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Update the status of an auction invoice with comprehensive validation
     *
     * @param AuctionInvoice $auctionInvoice
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(AuctionInvoice $auctionInvoice, Request $request): JsonResponse
    {
        // Use model validation rules
        $request->validate(AuctionInvoice::getStatusValidationRules());

        // Additional business logic validation
        if (!$auctionInvoice->canUpdateStatus($request->status)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition. Cannot change from ' .
                           $auctionInvoice->status . ' to ' . $request->status,
                'current_status' => $auctionInvoice->status,
                'valid_transitions' => $auctionInvoice->getValidStatusTransitions(),
            ], 422);
        }

        try {
            $success = $this->adminAuctionInvoiceService->updateInvoiceStatus(
                $auctionInvoice,
                $request->status,
                [
                    'payment_method' => $request->payment_method,
                    'transaction_id' => $request->transaction_id,
                    'notes' => $request->notes,
                    'admin_id' => auth()->id(),
                ]
            );

            if ($success) {
                $updatedInvoice = $auctionInvoice->fresh();

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice status updated successfully',
                    'data' => [
                        'id' => $updatedInvoice->id,
                        'status' => $updatedInvoice->status,
                        'status_badge' => $updatedInvoice->status_badge,
                        'paid_at' => $updatedInvoice->paid_at?->format('Y-m-d H:i:s'),
                        'valid_transitions' => $updatedInvoice->getValidStatusTransitions(),
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice status. Please check the logs for details.',
            ], 500);

        } catch (\Exception $e) {
            \Log::error('Invoice status update failed', [
                'invoice_id' => $auctionInvoice->id,
                'requested_status' => $request->status,
                'current_status' => $auctionInvoice->status,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the invoice status.',
            ], 500);
        }
    }


    /**
     * Export invoice data to CSV/Excel
     *
     * @param Request $request
     * @return Response
     */
    public function export(Request $request): Response
    {
        $filters = [
            'type' => $request->get('type'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'user_search' => $request->get('user_search'),
        ];

        $format = $request->get('format', 'csv');
        $exportData = $this->adminAuctionInvoiceService->exportInvoiceData($filters, $format);

        $filename = 'auction_invoices_' . now()->format('Y_m_d_H_i_s') . '.' . $format;

        return response($exportData['content'])
            ->header('Content-Type', $exportData['mime_type'])
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }


    /**
     * Send bulk payment reminders for selected overdue invoices
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendBulkReminders(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:auction_invoices,id',
            'reminder_type' => 'required|in:email,sms,both',
            'custom_message' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->adminAuctionInvoiceService->sendBulkPaymentReminders(
                $request->invoice_ids,
                $request->reminder_type,
                $request->custom_message,
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => "Reminders sent successfully to {$result['sent_count']} invoices",
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            \Log::error('Bulk reminder sending failed', [
                'invoice_ids' => $request->invoice_ids,
                'reminder_type' => $request->reminder_type,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminders. Please try again.',
            ], 500);
        }
    }
}
