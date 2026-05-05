<?php 

namespace App\Enums;

class PaymentType extends BaseEnum
{
    public const CART_PAYMENT = 'cart_payment';
    public const ORDER_REPAYMENT = 'order_repayment';
    public const WALLET_PAYMENT = 'wallet_payment';
    public const CAR_RESERVATION_PAYMENT = 'car_reservation_payment';
    public const CAR_INSPECTION_PAYMENT = 'car_inspection_payment';
    public const AUCTION_INVOICE_PAYMENT = 'auction_invoice_payment';
    public const AUCTION_INSURANCE_DEPOSIT = 'auction_insurance_deposit';
}