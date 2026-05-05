<?php

namespace App\Services\Payment;


use App\Enums\PaymentStatusEnum;
use App\Enums\PaymentType;
use App\Models\CarInspection;
use App\Models\CarReservation;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Models\ManualPaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Wallet;

class OfflinePaymentService
{
    public function processPayment(Request $request)
    {
        try {

            $manual_payment_method = ManualPaymentMethod::find($request->manual_payment_id);

            $manual_payment_data = array(
                'name'  => $request->name,
                'amount' => 0,
                'trx_id' => $request->trx_id,
                'photo'  => $request->photo != 0 ? $request->photo : null
            );

            if (isset($manual_payment_method)) {
                $manual_payment_data['method_name'] = $manual_payment_method->name;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => translate('Invalid payment method')
                ]);
            }

            switch ($request->type) {
                case PaymentType::CART_PAYMENT:
                    $this->handleCartPayment($request->payment_type_id, $manual_payment_data);
                    break;
                case PaymentType::ORDER_REPAYMENT:
                    $this->handleOrderPayment($request->payment_type_id, $manual_payment_data);
                    break;
                case PaymentType::CAR_INSPECTION_PAYMENT:
                    $this->handleCarInspectionPayment($request->payment_type_id, $manual_payment_data);
                    break;
                case PaymentType::CAR_RESERVATION_PAYMENT:
                    $this->handleCarReservationPayment($request->payment_type_id, $manual_payment_data);
                    break;
                case PaymentType::WALLET_PAYMENT:
                    $this->handleWalletRechargePayment($request->amount, $manual_payment_data);
                    break;
                case PaymentType::AUCTION_INVOICE_PAYMENT:
                    $this->handleAuctionInvoicePayment($request->payment_type_id, $manual_payment_data);
                    break;
                case PaymentType::AUCTION_INSURANCE_DEPOSIT:
                    $this->handleInsuranceDepositPayment($request->payment_type_id, $manual_payment_data);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => translate('Invalid payment type')
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => translate('Payment submitted. Please wait for response.')
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate payment request data
     */
    public function validatePaymentData(array $requestData): array
    {
        $errors = [];

        // Required fields
        $requiredFields = ['type', 'name', 'manual_payment_id', 'trx_id'];

        foreach ($requiredFields as $field) {
            if (empty($requestData[$field])) {
                $errors[] = "Field {$field} is required";
            }
        }

        // Validate payment type
        if (!empty($requestData['type']) && !PaymentType::isValid($requestData['type'])) {
            $errors[] = "Invalid payment type: {$requestData['type']}";
        }

        // Validate specific payment type requirements
        if (!empty($requestData['type'])) {
            switch ($requestData['type']) {
                case PaymentType::CART_PAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for cart payments';
                    } else {
                         // validated combined order exists
                        $combinedOrder = CombinedOrder::find($requestData['payment_type_id']);
                        if (!$combinedOrder) {
                            $errors[] = 'Combined order not found';
                        }
                    }

                    break;

                case PaymentType::ORDER_REPAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for cart payments';
                    } else {
                         $order = Order::find($requestData['payment_type_id']);
                        if (!$order) {
                            $errors[] = 'Order not found';
                        } elseif ($order->payment_status === 'paid') {
                            $errors[] = 'Order is already paid';
                        }
                    }

                    break;

                case PaymentType::WALLET_PAYMENT:
                    if (empty($requestData['amount']) || !is_numeric($requestData['amount']) || $requestData['amount'] <= 0) {
                        $errors[] = 'Valid amount is required for wallet payments';
                    }
                    break;
                case PaymentType::CAR_INSPECTION_PAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for car_inspection payments';
                    } else {
                         $inspection = CarInspection::find($requestData['payment_type_id']);
                        if (!$inspection) {
                            $errors[] = 'Car Inspection not found';
                        }
                    }

                    break;
                case PaymentType::CAR_RESERVATION_PAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for car_reservation payments';
                    } else {
                        $reservation = CarReservation::find($requestData['payment_type_id']);
                        if (!$reservation) {
                            $errors[] = 'Car Reservation not found';
                        }
                    }

                    break;
                case PaymentType::AUCTION_INVOICE_PAYMENT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for auction_invoice payments';
                    } else {
                        $auctionInvoice = \App\Models\AuctionInvoice::find($requestData['payment_type_id']);
                        if (!$auctionInvoice) {
                            $errors[] = 'Auction invoice not found';
                        } elseif ($auctionInvoice->status === 'paid') {
                            $errors[] = 'Auction invoice is already paid';
                        }
                    }

                    break;
                case PaymentType::AUCTION_INSURANCE_DEPOSIT:
                    if (empty($requestData['payment_type_id'])) {
                        $errors[] = 'payment_type_id is required for auction_insurance_deposit payments';
                    } else {
                        $insuranceDeposit = \App\Models\UserInsuranceDeposit::find($requestData['payment_type_id']);
                        if (!$insuranceDeposit) {
                            $errors[] = 'Insurance deposit not found';
                        } elseif ($insuranceDeposit->status === 'paid') {
                            $errors[] = 'Insurance deposit is already paid';
                        }
                    }

                    break;
            }
        }


        return $errors;
    }


    private function handleCartPayment($combined_order_id, $manual_payment_data)
    {
        $combined_order = CombinedOrder::find($combined_order_id);
        if (!isset($combined_order)) {
            throw new \Exception('Invalid order');
        }
        if ($combined_order->user_id != auth('api')->user()->id) {
            throw new \Exception('You are not authorized to pay for this order');
        }

        foreach ($combined_order->orders as $order) {
            $manual_payment_data['amount'] = $order->grand_total;
            $order->manual_payment_data =  $manual_payment_data;
            $order->payment_type = 'manual_payment';
            $order->payment_status = 'pending';
            $order->manual_payment = 1;

            $order->save();
        }
    }
    private function handleOrderPayment($order_id, $manual_payment_data)
    {
        $order = Order::find($order_id);
        if (!isset($order)) {
            throw new \Exception('Invalid order');
        }
        if ($order->payment_status == 'paid') {
            throw new \Exception('Order is already paid');
        }
        if ($order->user_id != auth('api')->user()->id) {
            throw new \Exception('You are not authorized to pay for this order');
        }

        $manual_payment_data['amount'] = $order->grand_total;

        $order->manual_payment_data =  $manual_payment_data;
        $order->payment_type = 'manual_payment';
        $order->payment_status = 'pending';
        $order->manual_payment = 1;

        $order->save();
    }

    private function handleCarInspectionPayment($inspection_id, $manual_payment_data)
    {
        $inspection = CarInspection::find($inspection_id);
        if (!isset($inspection)) {
            throw new \Exception('Invalid inspection');
        }
        if ($inspection->payment && $inspection->payment->status == 'paid') {
            throw new \Exception('Inspection is already paid');
        }
        if ($inspection->requester->id != auth('api')->user()->id) {
            throw new \Exception('You are not authorized to pay for this inspection');
        }

        $manual_payment_data['amount'] = $inspection->inspectionType->price;

        $inspection->payment()->updateOrCreate([], [
            'method'    => $manual_payment_data['method_name'],
            'is_manual_payment' => true,
            'status'    => PaymentStatusEnum::PENDING,
            'amount'   => $manual_payment_data['amount'],
            'transaction_id'   => $manual_payment_data['trx_id'],
            'details'   => $manual_payment_data,
        ]);
        $inspection->save();
    }

    private function handleCarReservationPayment($reservation_id, $manual_payment_data)
    {
        $reservation = CarReservation::find($reservation_id);
        if (!isset($reservation)) {
            throw new \Exception('Invalid reservation');
        }
        if ($reservation->payment_status == 'paid') {
            throw new \Exception('Reservation is already paid');
        }

        $manual_payment_data['amount'] = get_setting('car_reservation_amount', 500);
        $reservation->payment()->updateOrCreate([], [
            'method'    => $manual_payment_data['method_name'],
            'is_manual_payment' => true,
            'status'    => PaymentStatusEnum::PENDING,
            'amount'   => $manual_payment_data['amount'],
            'transaction_id'   => $manual_payment_data['trx_id'],
            'details'   => $manual_payment_data,
        ]);

        $reservation->save();
    }
    private function handleWalletRechargePayment($amount, $manual_payment_data)
    {
        if (!get_setting('recharge_wallet_active')) {
            throw new \Exception(translate('Recharging credit is not available now'));
        }

        $wallet = new Wallet;
        $wallet->user_id = auth('api')->user()->id;
        $wallet->amount = $amount;
        $wallet->payment_method = 'manual_payment';
        $wallet->payment_details = $manual_payment_data;
        $wallet->approval = 0;
        $wallet->offline_payment = 1;
        $wallet->save();
    }

    private function handleAuctionInvoicePayment($invoice_id, $manual_payment_data)
    {
        $invoice = \App\Models\AuctionInvoice::find($invoice_id);
        if (!isset($invoice)) {
            throw new \Exception('Invalid auction invoice');
        }
        if ($invoice->status == 'paid') {
            throw new \Exception('Invoice is already paid');
        }
        if ($invoice->user_id != auth('api')->user()->id) {
            throw new \Exception('You are not authorized to pay for this invoice');
        }

        $payment = $invoice->payment()->updateOrCreate([], [
            'method' => $manual_payment_data['method_name'],
            'status' => PaymentStatusEnum::PENDING,
            'transaction_id' => $manual_payment_data['trx_id'],
            'reference_id' => $manual_payment_data['reference_id'] ?? null,
            'amount' => $invoice->amount,
            'details' => $manual_payment_data,
            'paid_at' => now()
        ]);

        // Update invoice status
        $invoice->status = PaymentStatusEnum::PENDING;
        $invoice->payment_id = $payment->id;
        $invoice->paid_at = now();
        $invoice->save();

        // Log audit trail
        \App\Models\AuctionAuditLog::create([
            'auction_room_id' => $invoice->auctionItem->auction_room_id,
            'auction_item_id' => $invoice->auction_item_id,
            'user_id' => $invoice->user_id,
            'action' => 'invoice_paid',
            'details' => [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,
                'amount' => $invoice->amount,
                'payment_method' => $manual_payment_data['method_name'],
                'transaction_id' => $manual_payment_data['trx_id']
            ],
            'ip_address' => request()->ip()
        ]);
    }

    private function handleInsuranceDepositPayment($deposit_id, $manual_payment_data)
    {
        $deposit = \App\Models\UserInsuranceDeposit::findOrFail($deposit_id);
        if (!isset($deposit)) {
            throw new \Exception('Invalid insurance deposit');
        }
        if ($deposit->status == 'paid') {
            throw new \Exception('Deposit is already paid');
        }
        if ($deposit->user_id != auth('api')->user()->id) {
            throw new \Exception('You are not authorized to pay for this deposit');
        }

        $payment = $deposit->payment()->updateOrCreate([], [
            'method' => $manual_payment_data['method_name'],
            'status' => PaymentStatusEnum::PENDING,
            'transaction_id' => $manual_payment_data['trx_id'],
            'reference_id' => $manual_payment_data['reference_id'] ?? null,
            'amount' => $deposit->amount,
            'details' => $manual_payment_data,
            'paid_at' => now()
        ]);

        // Update deposit status
        $deposit->status = PaymentStatusEnum::PENDING;
        $deposit->payment_id = $payment->id;
        $deposit->paid_at = now();
        $deposit->save();

        // Log audit trail
        \App\Models\AuctionAuditLog::create([
            'user_id' => $deposit->user_id,
            'action' => 'insurance_deposit_paid',
            'details' => [
                'deposit_id' => $deposit->id,
                'amount' => $deposit->amount,
                'payment_method' => $manual_payment_data['method_name'],
                'transaction_id' => $manual_payment_data['trx_id']
            ],
            'ip_address' => request()->ip()
        ]);
    }
}
