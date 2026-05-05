<?php

namespace App\Http\Controllers\Api\V2\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayInsuranceDepositRequest;
use App\Services\InsuranceDepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsuranceDepositController extends Controller
{
    public function __construct(
        private InsuranceDepositService $insuranceDepositService
    ) {}

    /**
     * Pay insurance deposit
     * POST /api/v2/auction/customer/insurance-deposit
     */
    public function store(PayInsuranceDepositRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $amount = get_setting('insurance_deposit_amount', 500.00);
            $provider = $request->validated('provider');

            // Check if user already has an active deposit
            if ($user->hasInsuranceDeposit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active insurance deposit',
                    'data' => $user->insuranceDeposit
                ], 409);
            }

            // Create deposit record
            $deposit = $this->insuranceDepositService->createDeposit($user, $amount);

            // Prepare payment request data for the existing payment system
            $paymentRequestData = [
                'payment_type' => \App\Enums\PaymentType::AUCTION_INSURANCE_DEPOSIT,
                'provider' => $provider,
                'payment_type_id' => $deposit->id,
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
                    'data' => $deposit->fresh()->load('payment')
                ], 201);
            } else {
                // Payment failed, delete the deposit record
                $deposit->delete();

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }

        } catch (\Exception $e) {
            \Log::error('Insurance deposit payment failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create insurance deposit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deposit status
     * GET /api/v2/auction/customer/insurance-deposit
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $deposit = $user->insuranceDeposit;

        if (!$deposit) {
            return response()->json([
                'success' => true,
                'message' => 'No insurance deposit found',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $deposit->load('payment', 'refundPayment')
        ]);
    }

    /**
     * Request refund
     * POST /api/v2/auction/customer/insurance-deposit/refund
     */
    public function refund(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if(!$user->hasInsuranceDeposit()){
                 return response()->json([
                    'success' => false,
                    'message' => 'You do not have any active Insurance deposit',
                ], 422);
            }

            if($user->hasUnpaidAuctionInvoices()){
                     return response()->json([
                    'success' => false,
                    'message' => 'Cannot refund deposit with unpaid invoices',

                ], 422);
            }
            $deposit = $user->paidInsuranceDeposit();

            if($deposit->refund_requested){
                 return response()->json([
                    'success' => false,
                    'message' => 'You have already requested a refund',
                ], 422);
            }

            $success = $this->insuranceDepositService->requestRefund($user, $deposit);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process refund request'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Refund request processed successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check eligibility
     * GET /api/v2/auction/customer/insurance-deposit/eligibility
     */
    public function eligibility(Request $request): JsonResponse
    {
        $user = $request->user();
        $eligibility = $this->insuranceDepositService->checkEligibility($user);

        return response()->json([
            'success' => true,
            'data' => $eligibility
        ]);
    }
}
