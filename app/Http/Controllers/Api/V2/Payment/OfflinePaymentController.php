<?php

namespace App\Http\Controllers\Api\V2\Payment;

use App\Enums\PaymentType;
use App\Models\CarInspection;
use App\Models\CarReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AuctionInvoice;
use App\Models\Car;
use App\Models\CarInspectionType;
use App\Services\InsuranceDepositService;
use App\Services\OrderService;
use App\Services\Payment\OfflinePaymentService;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\JsonResponse;

class OfflinePaymentController extends Controller
{
    private OfflinePaymentService $offlinePaymentService;
    private OrderService $orderService;
    private InsuranceDepositService $insuranceDepositService;

   public function __construct(OfflinePaymentService $offlinePaymentService, OrderService $orderService, InsuranceDepositService $insuranceDepositService)
    {
        $this->middleware('auth:sanctum');
        $this->offlinePaymentService = $offlinePaymentService;
        $this->orderService = $orderService;
        $this->insuranceDepositService = $insuranceDepositService;
    }

    public function submit(Request $request) {
        try{
            $validationErrors = $this->offlinePaymentService->validatePaymentData($request->all());
            if (!empty($validationErrors)) {
                return $this->errorResponse(implode(', ', $validationErrors));
            }

            // Process payment
            $result = $this->offlinePaymentService->processPayment($request);

            return $result;

       } catch (Exception $e) {
            Log::error('Offline Payment processing error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Payment processing failed: ' . $e->getMessage());
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
                'address_id' => ['required', 'exists:addresses,id'],
                'cod' => ['sometimes', 'boolean'],
            ]);

            // Save the order
            $combined_order_id = $this->orderService->storeOrder($user->id, $request->address_id, $request->cod ?? false, $request->notes);

            $request->merge(['type' => PaymentType::CART_PAYMENT, 'payment_type_id' => $combined_order_id]);

            $paymentResponse = $this->submit($request);

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

            $request->merge(['type' => PaymentType::CAR_RESERVATION_PAYMENT, 'payment_type_id' => $reservation->id]);

            $paymentResponse = $this->submit($request);
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
                'type' => PaymentType::CAR_INSPECTION_PAYMENT,
                'payment_type_id' => $inspection->id
            ]);

            $paymentResponse = $this->submit($request);
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
                ], 409);
            }

            DB::beginTransaction();
            // Create deposit record
            $deposit = $this->insuranceDepositService->createDeposit($user, $amount);

            // process payment
            $request->merge(['type' => PaymentType::AUCTION_INSURANCE_DEPOSIT, 'payment_type_id' => $deposit->id]);

            $paymentResponse = $this->submit($request);
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
            $request->merge(['type' => PaymentType::AUCTION_INVOICE_PAYMENT, 'payment_type_id' => $auctionInvoice->id]);

            $paymentResponse = $this->submit($request);
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
