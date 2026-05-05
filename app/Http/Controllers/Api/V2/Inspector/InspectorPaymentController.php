<?php

namespace App\Http\Controllers\Api\V2\Inspector;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Inspector\PaymentResource;
use App\Http\Resources\V2\Inspector\PaymentSummaryResource;
use App\Models\CarInspection;
use App\Models\CarInspector;
use App\Models\CarInspectorPaymentHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class InspectorPaymentController extends Controller
{
    /**
     * Get payment history with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $inspector = $request->user()->carInspector;

        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        $request->validate([
            'status' => ['sometimes', 'string', Rule::in([
                CarInspectorPaymentHistory::STATUS_PENDING,
                CarInspectorPaymentHistory::STATUS_COMPLETED,
                CarInspectorPaymentHistory::STATUS_FAILED,
                CarInspectorPaymentHistory::STATUS_CANCELLED
            ])],
            'type' => ['sometimes', 'string', Rule::in([
                CarInspectorPaymentHistory::TYPE_EARNING,
                CarInspectorPaymentHistory::TYPE_PAYMENT,
                CarInspectorPaymentHistory::TYPE_ADJUSTMENT
            ])],
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|in:created_at,amount,status,type',
            'sort_order' => 'sometimes|string|in:asc,desc'
        ]);

        $query = CarInspectorPaymentHistory::where('car_inspector_id', $inspector->id)
            ->with('processedBy');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $payments = $query->paginate($perPage);

        return response()->json([
            'data' => PaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'from' => $payments->firstItem(),
                'to' => $payments->lastItem(),
            ],
            'links' => [
                'first' => $payments->url(1),
                'last' => $payments->url($payments->lastPage()),
                'prev' => $payments->previousPageUrl(),
                'next' => $payments->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Get payment summary and statistics
     */
    public function summary(Request $request): JsonResponse
    {
        $inspector = $request->user()->carInspector;

        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        $summaryData = $this->calculatePaymentSummary($inspector->id);

        return response()->json([
            'data' => new PaymentSummaryResource($summaryData)
        ]);
    }

    /**
     * Get specific payment details
     */
    public function show(Request $request, int $paymentId): JsonResponse
    {
        $inspector = $request->user()->carInspector;

        if (!$inspector) {
            return response()->json([
                'error' => [
                    'message' => 'Inspector profile not found',
                    'code' => 'INSPECTOR_NOT_FOUND'
                ]
            ], 404);
        }

        $payment = CarInspectorPaymentHistory::where('car_inspector_id', $inspector->id)
            ->where('id', $paymentId)
            ->with('processedBy')
            ->first();

        if (!$payment) {
            return response()->json([
                'error' => [
                    'message' => 'Payment not found',
                    'code' => 'PAYMENT_NOT_FOUND'
                ]
            ], 404);
        }

        return response()->json([
            'data' => new PaymentResource($payment)
        ]);
    }

    /**
     * Calculate payment summary statistics
     */
    private function calculatePaymentSummary(int $inspectorId): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();



        // Total payments received (all completed payments)
        $totalPaymentsReceived = CarInspectorPaymentHistory::where('car_inspector_id', $inspectorId)
            ->where('type', CarInspectorPaymentHistory::TYPE_PAYMENT)
            ->where('status', CarInspectorPaymentHistory::STATUS_COMPLETED)
            ->sum('amount');



        // Pending payments (earnings that haven't been paid out yet)
        $pendingPayments = CarInspector::find($inspectorId)->admin_to_pay;

        // Total earnings (all completed earnings)
        $totalEarnings = $totalPaymentsReceived + $pendingPayments;

        // Current balance (total earnings - total payments received)
        $currentBalance = $totalEarnings - $totalPaymentsReceived;

        // This month's earnings
        $thisMonthEarnings = CarInspectorPaymentHistory::where('car_inspector_id', $inspectorId)
            ->where('type', CarInspectorPaymentHistory::TYPE_EARNING)
            ->where('status', CarInspectorPaymentHistory::STATUS_COMPLETED)
            ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])
            ->sum('amount');

        // This month's payments received
        $thisMonthPaymentsReceived = CarInspectorPaymentHistory::where('car_inspector_id', $inspectorId)
            ->where('type', CarInspectorPaymentHistory::TYPE_PAYMENT)
            ->where('status', CarInspectorPaymentHistory::STATUS_COMPLETED)
            ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])
            ->sum('amount');

        // Last payment received
        $lastPayment = CarInspectorPaymentHistory::where('car_inspector_id', $inspectorId)
            ->where('type', CarInspectorPaymentHistory::TYPE_PAYMENT)
            ->where('status', CarInspectorPaymentHistory::STATUS_COMPLETED)
            ->orderBy('created_at', 'desc')
            ->first();

        $lastPaymentData = null;
        if ($lastPayment) {
            $lastPaymentData = [
                'amount' => $lastPayment->amount,
                'date' => $lastPayment->created_at->toISOString(),
                'payment_method' => $lastPayment->payment_method,
            ];
        }

        return [
            'total_earnings' => round($totalEarnings, 2),
            'total_payments_received' => round($totalPaymentsReceived, 2),
            'pending_payments' => round($pendingPayments ?? 0, 2),
            'current_balance' => round($currentBalance, 2),
            'this_month' => [
                'earnings' => round($thisMonthEarnings, 2),
                'payments_received' => round($thisMonthPaymentsReceived, 2),
            ],
            'last_payment' => $lastPaymentData,
        ];
    }
}
