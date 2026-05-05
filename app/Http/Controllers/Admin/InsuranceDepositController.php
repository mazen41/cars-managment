<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\UserInsuranceDeposit;
use App\Services\InsuranceDepositService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class InsuranceDepositController extends Controller
{
    protected InsuranceDepositService $insuranceDepositService;

    public function __construct(InsuranceDepositService $insuranceDepositService)
    {
        $this->middleware(['permission:view_insurance_deposits'])->only(['index', 'show']);
        $this->middleware(['permission:manage_insurance_deposits'])->only(['refund', 'updatePaymentStatus']);
        $this->insuranceDepositService = $insuranceDepositService;
    }

    /**
     * Display a paginated list of insurance deposits with filters
     * GET /admin/insurance-deposits
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Validate request parameters
        $validated = $request->validate([
            'status' => 'nullable|in:pending,paid,refunded',
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|in:amount,date,paid_at,customer_name,status',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Prepare filters array
        $filters = [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort_by' => $request->input('sort_by'),
            'sort_direction' => $request->input('sort_direction', 'asc'),
        ];

        // Get paginated deposits
        $perPage = $request->input('per_page', 20);
        $deposits = $this->insuranceDepositService->getFilteredDeposits($filters, $perPage);

        // Get statistics
        $statistics = $this->insuranceDepositService->getDepositStatistics($filters);

        return view('backend.auctions.customer-insurance-deposits.index', compact('deposits', 'statistics', 'filters'));
    }

    /**
     * Display detailed information about a specific deposit
     * GET /admin/insurance-deposits/{deposit}
     *
     * @param UserInsuranceDeposit $deposit
     * @return View
     */
    public function show(UserInsuranceDeposit $deposit): View
    {
        // Load all necessary relationships with eager loading
        $deposit->load(['user', 'payment', 'refundPayment']);

        // Prepare structured data for the view
        $depositDetails = [
            'customer_info' => [
                'id' => $deposit->user->id,
                'name' => $deposit->user->name,
                'email' => $deposit->user->email,
                'phone' => $deposit->user->phone ?? 'N/A',
            ],
            'deposit_info' => [
                'amount' => $deposit->amount,
                'status' => $deposit->status,
                'paid_at' => $deposit->paid_at,
                'refunded_at' => $deposit->refunded_at,
                'created_at' => $deposit->created_at,
                'updated_at' => $deposit->updated_at,
            ],
            'payment_details' => [
                'method' => $deposit->payment->method ?? 'N/A',
                'transaction_id' => $deposit->payment->transaction_id ?? 'N/A',
                'reference_id' => $deposit->payment->reference_id ?? 'N/A',
                'paid_at' => $deposit->payment->paid_at ?? null,
            ],
            'refund_details' => $deposit->refunded_at ? [
                'refund_method' => $deposit->refundPayment->method ?? 'N/A',
                'refund_transaction_id' => $deposit->refundPayment->transaction_id ?? 'N/A',
                'refund_reference_id' => $deposit->refundPayment->reference_id ?? 'N/A',
                'refunded_at' => $deposit->refunded_at,
            ] : null,
            'refund_requested' => $deposit->refund_requested,
            'refund_requested_at' => $deposit->refund_requested_at,
            'can_be_refunded' => $deposit->canBeRefunded(),
            'can_update_payment' => $deposit->status == PaymentStatusEnum::PENDING && $deposit->payment->status == PaymentStatusEnum::PENDING
        ];

        return view('backend.auctions.customer-insurance-deposits.show', compact('deposit', 'depositDetails'));
    }

    /**
     * Process a refund for a deposit
     * POST /admin/insurance-deposits/{deposit}/refund
     *
     * @param Request $request
     * @param UserInsuranceDeposit $deposit
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function refund(Request $request, UserInsuranceDeposit $deposit)
    {
        $request->validate(
            ['transaction_id' => 'required|string']
        );

        // Validate that the deposit can be refunded
        if ($deposit->status !== 'paid') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only paid deposits can be refunded.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Only paid deposits can be refunded.');
        }

        //  Prevent duplicate refund attempts
        if ($deposit->refunded_at !== null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This deposit has already been refunded.',
                ], 422);
            }

            return redirect()->back()->with('error', 'This deposit has already been refunded.');
        }

        try {
            // Process the refund through the service
            $refundPayment = $this->insuranceDepositService->processRefund($deposit, $request->transaction_id);

            if (!$refundPayment) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to process refund. Please try again.',
                    ], 500);
                }

                return redirect()->back()->with('error', 'Failed to process refund. Please try again.');
            }

            // Status updated and refund metadata recorded by service
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Refund processed successfully.',
                    'data' => [
                        'deposit_id' => $deposit->id,
                        'status' => $deposit->fresh()->status,
                        'refunded_at' => $deposit->fresh()->refunded_at,
                        'refund_payment_id' => $deposit->fresh()->refund_payment_id,
                    ],
                ]);
            }

            return redirect()->route('insurance-deposits.show', $deposit)
                ->with('success', 'Refund processed successfully.');
        } catch (\Exception $e) {
            \Log::error('Refund processing failed', [
                'deposit_id' => $deposit->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing the refund.',
                ], 500);
            }

            return redirect()->back()->with('error', 'An error occurred while processing the refund.');
        }
    }

    /**
     * Update Payment status for pending insurance deposits
     * POST /admin/insurance-deposits/update-payment
     * @param Request $request
     * @param UserInsuranceDeposit $deposit
     * @return \Illuminate\Http\JsonResponse
     */

    public function updatePaymentStatus(Request $request)
    {
        try{
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|exists:user_insurance_deposits',
                'status' => 'required|in:paid,cancelled'
            ]
        );

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message'=> $validator->errors()->first()
            ], 422);
        }

        $deposit = UserInsuranceDeposit::find($request->id);
        $success = $this->insuranceDepositService->updatePaymentStatus($deposit, $request->status);

        if($success){
             return response()->json([
                    'success' => true,
                    'message' => 'Insurance deposit updated successfully',
                ], 200);
        }

         return response()->json([
            'success' => false,
            'message' => 'Only pending deposits can be updated',
        ], 422);

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: '. $e->getMessage(),
            ], 500);
        }

    }

    /**
     * Get deposit statistics for AJAX requests
     * GET /admin/insurance-deposits/statistics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'status' => 'nullable|in:pending,paid,refunded',
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        // Prepare filters array
        $filters = [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        // Create a cache key based on filters
        $cacheKey = 'insurance_deposit_statistics_' . md5(json_encode($filters));

        // Cache statistics for 5 minutes
        $statistics = Cache::remember($cacheKey, 300, function () use ($filters) {
            return $this->insuranceDepositService->getDepositStatistics($filters);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_count' => $statistics['total_count'],
                'paid_amount' => $statistics['paid_amount'],
                'refunded_amount' => $statistics['refunded_amount'],
                'pending_count' => $statistics['pending_count'],
            ],
        ]);
    }
}
