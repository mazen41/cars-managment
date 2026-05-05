<?php

namespace App\Http\Controllers\Api\V2\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentService;
use App\DTOs\Payment\PaymentRequest;
use App\Enums\PaymentType;
use App\Exceptions\CheckoutException;
use App\Models\AuctionInvoice;
use App\Models\Car;
use App\Models\CarInspection;
use App\Models\CarInspectionType;
use App\Models\CarReservation;
use App\Services\InsuranceDepositService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private PaymentService $paymentService;
    private OrderService $orderService;
    private InsuranceDepositService $insuranceDepositService;

    public function __construct(PaymentService $paymentService, OrderService $orderService, InsuranceDepositService $insuranceDepositService)
    {
        $this->middleware('auth:sanctum');
        $this->paymentService = $paymentService;
        $this->orderService = $orderService;
        $this->insuranceDepositService = $insuranceDepositService;
    }

    /**
     * Process payment for any provider
     */
    public function pay(Request $request): JsonResponse
    {
        try {
            // Validate required fields
            $validationErrors = $this->paymentService->validatePaymentData($request->all());
            if (!empty($validationErrors)) {
                return $this->errorResponse(implode(', ', $validationErrors));
            }

            // Create payment request DTO
            $paymentRequest = PaymentRequest::fromRequest($request);

            // Validate payment request
            $requestErrors = $paymentRequest->validate();
            if (!empty($requestErrors)) {
                return $this->errorResponse(implode(', ', $requestErrors));
            }

            // Process payment
            $result = $this->paymentService->processPayment($paymentRequest);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request): JsonResponse
    {
        try {
            $provider = $request->input('provider');
            $requestId = $request->input('request_id');

            if (empty($provider) || empty($requestId)) {
                return $this->errorResponse('Provider and request_id are required');
            }

            $response = $this->paymentService->checkPaymentStatus($provider, $requestId);

            return response()->json([
                'result' => $response->success,
                'message' => translate($response->message),
                'data' => $response->toArray()
            ]);
        } catch (Exception $e) {
            Log::error('Payment status check error: ' . $e->getMessage(), [
                'provider' => $request->input('provider'),
                'request_id' => $request->input('request_id')
            ]);

            return $this->errorResponse('Status check failed: ' . $e->getMessage());
        }
    }

    /**
     * Cart payment (One Step)
     */
    public function cartPayment(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            DB::beginTransaction();

            // Validate order
            $request->validate([
                'address_id' => ['required', 'exists:addresses,id']
            ]);

            // Save the order
            $combined_order_id = $this->orderService->storeOrder($user->id, $request->address_id,  false, $request->notes);

            $request->merge(['payment_type' => PaymentType::CART_PAYMENT, 'payment_type_id' => $combined_order_id]);

            $paymentResponse = $this->pay($request);

            $paymentResponse = $paymentResponse->getData(true);

            if ($paymentResponse['success']) {

                DB::commit();

                return $this->successResponse($paymentResponse['message']);

            } else {
                DB::rollback();

                return $this->errorResponse($paymentResponse['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 422);

        } catch (CheckoutException $e) {

            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 400);

        } catch (Exception $e) {

            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 500);
        }
    }
    /**
     * Car Reservation Payment (One step)
     */
    public function carReservationPayment(Request $request)
    {

        try {

            DB::beginTransaction();

            $request->validate([
                'car_id' => [
                    'required',
                    'exists:cars,id',
                    function ($attribute, $value, $fail) {
                        $car = Car::find($value);
                        if ($car && !$car->canBeReserved()) {
                            $fail('This car is not available for reservation.');
                        }
                    },
                ],
                'provider' => ['required', 'string'],
                'code' => ['sometimes', 'string'],
            ]);

            $car = Car::findOrFail($request->car_id);
            $user_id = $request->user()->id;

            if (!$car->canBeReserved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This car is not available for reservation'
                ], 400);
            }

            // Check if user already has an active reservation for this car
            $existingReservation = CarReservation::where('user_id', $user_id)
                ->where('car_id', $car->id)
                ->active()
                ->first();

            if ($existingReservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active reservation for this car'
                ], 400);
            }

            $reservation = $car->createReservation(
                $user_id,
                $request->notes,
            );

            $request->merge(['payment_type' => PaymentType::CAR_RESERVATION_PAYMENT, 'payment_type_id' => $reservation->id]);

            $paymentResponse = $this->pay($request);
            $paymentResponse = $paymentResponse->getData(true);
            \Log::info('Payment response', $paymentResponse);
           if ($paymentResponse['success']) {

                DB::commit();

                return $this->successResponse($paymentResponse['message']);

            } else {
                DB::rollback();

                return $this->errorResponse($paymentResponse['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Car Inspection Payment (One Step)
     */

    public function carInspectionPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate(
                [
                    'car_id' => ['required', 'integer', 'exists:cars,id'],
                    "inspection_type_id" => ['required', 'integer', 'exists:car_inspection_types,id'],
                    "inspector_id"  => ['sometimes', 'integer', 'exists:car_inspectors,id']
                ]
            );


            $data = $request->only([
                'car_id',
                'inspection_type_id',
                'inspector_id'
            ]);

            $car = Car::find($data['car_id']);

            //check if car is available
            if ($car->isSold() || !$car->isPublished()) {
                return  response()->json([
                    "success" => false,
                    "message" => "Car is not available",
                ], 422,);
            }

            $inspection_type = CarInspectionType::find($data['inspection_type_id']);
            if ($inspection_type->is_system_default){
                $data['inspector_id'] = null; // system default inspection types should not have inspector assigned at creation
                } else {
                    if (!isset($data['inspector_id'])) {
                        return response()->json([
                            "success" => false,
                            "message" => "Inspector is required for this inspection type",
                        ], 422);
                    }
            }

            $data["requested_by"] = $request->user()->id;

            //Check if user has ongoing inspections for this car
            $pendingInspections = CarInspection::byCar($data['car_id'])->byRequester($data["requested_by"])->byStatus('pending')->count();
            if ($pendingInspections > 0) {
                throw new Exception("You already have a pending inspection for this car");
            }

            $inspection = CarInspection::create($data);

            $request->merge([
                'payment_type' => PaymentType::CAR_INSPECTION_PAYMENT,
                'payment_type_id' => $inspection->id
            ]);

            $paymentResponse = $this->pay($request);
            $paymentResponse = $paymentResponse->getData(true);

           if ($paymentResponse['success']) {

                DB::commit();

                return $this->successResponse($paymentResponse['message']);

            } else {
                DB::rollback();

                return $this->errorResponse($paymentResponse['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Auction Insurance Deposit Payment (One step)
     */

    public function insuranceDepositPayment(Request $request)
    {
        try {

            $user = $request->user();
            $amount = get_setting('insurance_deposit_amount', 500.00);
            // Validate the request
            if ($user->hasInsuranceDeposit()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active insurance deposit',
                    'data' => $user->insuranceDeposit
                ], 409);
            }

            DB::beginTransaction();
            // Create deposit record
            $deposit = $this->insuranceDepositService->createDeposit($user, $amount);

            // process payment
            $request->merge(['payment_type' => PaymentType::AUCTION_INSURANCE_DEPOSIT, 'payment_type_id' => $deposit->id]);

            $paymentResponse = $this->pay($request);
            $paymentResponse = $paymentResponse->getData(true);

            if ($paymentResponse['success']) {

                DB::commit();

                return $this->successResponse($paymentResponse['message']);

            } else {
                DB::rollback();

                return $this->errorResponse($paymentResponse['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Auction Invoice Payment (One Step)
     */

    public function auctionInvoicePayment(Request $request)
    {
        try {
            $request->validate([
                'auction_invoice_id' => 'required|exists:auction_invoices,id'
            ]);
            $auctionInvoice = AuctionInvoice::find($request->input('auction_invoice_id'));
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
                ], 409);
            }

            DB::beginTransaction();

            // process payment
            $request->merge(['payment_type' => PaymentType::AUCTION_INVOICE_PAYMENT, 'payment_type_id' => $auctionInvoice->id]);

            $paymentResponse = $this->pay($request);
            $paymentResponse = $paymentResponse->getData(true);

           if ($paymentResponse['success']) {

                DB::commit();

                return $this->successResponse($paymentResponse['message']);

            } else {
                DB::rollback();

                return $this->errorResponse($paymentResponse['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 422);
        } catch (Exception $e) {

            DB::rollBack();

            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create success response
     */
    private function successResponse(string $message, array $data = []): JsonResponse
    {
        return response()->json(array_merge([
            'result' => true,
            'message' => $message
        ], $data));
    }

    /**
     * Create error response
     */
    private function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }
}
