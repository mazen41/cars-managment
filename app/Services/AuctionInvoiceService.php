<?php

namespace App\Services;

use App\Models\AuctionItem;
use App\Models\AuctionInvoice;
use App\Models\AuctionRoom;
use App\Models\User;
use App\Models\Payment;
use App\Models\AuctionAuditLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PDF;

class AuctionInvoiceService
{
    /**
     * Generate a buyer invoice for a sold auction item
     *
     * @param AuctionItem $item
     * @param User $buyer
     * @param float $amount
     * @return AuctionInvoice
     */
    public function generateBuyerInvoice(AuctionItem $item, User $buyer, float $amount): AuctionInvoice
    {
        return DB::transaction(function () use ($item, $buyer, $amount) {
            $invoice = AuctionInvoice::create([
                'auction_item_id' => $item->id,
                'invoice_type' => 'buyer_payment',
                'user_id' => $buyer->id,
                'amount' => $amount,
                'commission_amount' => null,
                'net_amount' => $amount,
                'status' => 'pending',
                'due_date' => now()->addDays(2), // 2 days to pay
            ]);

            // Log the invoice generation
            AuctionAuditLog::create([
                'auction_room_id' => $item->auction_room_id,
                'auction_item_id' => $item->id,
                'user_id' => $buyer->id,
                'action' => 'buyer_invoice_generated',
                'details' => [
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'due_date' => $invoice->due_date,
                ],
                'ip_address' => request()->ip(),
            ]);

            return $invoice;
        });
    }

    /**
     * Generate a seller payout invoice for a sold auction item
     *
     * @param AuctionItem $item
     * @param User $seller
     * @param float $amount
     * @param float $commission
     * @return AuctionInvoice
     */
    public function generateSellerPayout(AuctionItem $item, User $seller, float $amount, float $commission): AuctionInvoice
    {
        return DB::transaction(function () use ($item, $seller, $amount, $commission) {
            $netAmount = $amount - $commission;

            $invoice = AuctionInvoice::create([
                'auction_item_id' => $item->id,
                'invoice_type' => 'seller_payout',
                'user_id' => $seller->id,
                'amount' => $amount,
                'commission_amount' => $commission,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'due_date' => null, // Payout doesn't have a due date
            ]);

            // Log the invoice generation
            AuctionAuditLog::create([
                'auction_room_id' => $item->auction_room_id,
                'auction_item_id' => $item->id,
                'user_id' => $seller->id,
                'action' => 'seller_payout_generated',
                'details' => [
                    'invoice_id' => $invoice->id,
                    'amount' => $amount,
                    'commission' => $commission,
                    'net_amount' => $netAmount,
                ],
                'ip_address' => request()->ip(),
            ]);

            return $invoice;
        });
    }

    /**
     * Mark an invoice as paid
     *
     * @param AuctionInvoice $invoice
     * @param Payment $payment
     * @return bool
     */
    public function markInvoicePaid(AuctionInvoice $invoice, Payment $payment): bool
    {
        return DB::transaction(function () use ($invoice, $payment) {
            $invoice->update([
                'status' => 'paid',
                'payment_id' => $payment->id,
                'paid_at' => now(),
            ]);

            // Log the payment
            AuctionAuditLog::create([
                'auction_room_id' => $invoice->auctionItem->auction_room_id,
                'auction_item_id' => $invoice->auction_item_id,
                'user_id' => $invoice->user_id,
                'action' => 'invoice_paid',
                'details' => [
                    'invoice_id' => $invoice->id,
                    'invoice_type' => $invoice->invoice_type,
                    'payment_id' => $payment->id,
                    'amount' => $invoice->amount,
                ],
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Calculate commission for an auction room
     *
     * @param AuctionRoom $room
     * @param float $amount
     * @return float
     */
    public function calculateCommission(AuctionRoom $room, float $amount): float
    {
        $commission = ($amount * $room->commission_percentage) / 100;
        return round($commission, 2);
    }

    /**
     * Get unpaid invoices for a user
     *
     * @param User $user
     * @return Collection
     */
    public function getUserUnpaidInvoices(User $user): Collection
    {
        return AuctionInvoice::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['auctionItem.car', 'auctionItem.auctionRoom'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Generate PDF for an invoice
     *
     * @param AuctionInvoice $invoice
     * @return string
     */
    public function generateInvoicePdf(AuctionInvoice $invoice): string
    {
        $invoice->load([
            'user',
            'auctionItem.car',
            'auctionItem.auctionRoom',
            'payment'
        ]);

        $templateName = $invoice->isBuyerInvoice() ? 'buyer_invoice' : 'seller_payout';
        $options = get_pdf_options();

        $pdf = PDF::loadView(
            "backend.auctions.invoices.pdf.{$templateName}",
            compact('invoice', 'options'),
            [],
            [
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]
        );

        return $pdf->output();
    }

    /**
     * Generate proper filename for invoice PDF
     *
     * @param AuctionInvoice $invoice
     * @return string
     */
    public function generatePdfFilename(AuctionInvoice $invoice): string
    {
        $type = $invoice->isBuyerInvoice() ? 'buyer_invoice' : 'seller_payout';
        $date = $invoice->created_at->format('Y_m_d');
        $invoiceId = str_pad($invoice->id, 6, '0', STR_PAD_LEFT);

        return sprintf('%s_%s_%s.pdf', $type, $invoiceId, $date);
    }
}
