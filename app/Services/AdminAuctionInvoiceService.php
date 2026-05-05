<?php

namespace App\Services;

use App\Models\AuctionInvoice;
use App\Models\AuctionAuditLog;
use App\Models\User;
use App\Notifications\AuctionNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class AdminAuctionInvoiceService
{
    /**
     * Get filtered and paginated invoices for admin view
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getFilteredInvoices(array $filters): LengthAwarePaginator
    {
        $query = AuctionInvoice::with([
            'user:id,name,email,phone',
            'auctionItem:id,auction_room_id,car_id',
            'auctionItem.car:id,vin,model_id,brand_id,color_id,manufacture_year',
            'auctionItem.auctionRoom:id,name',
            'payment:id,method,transaction_id'
        ]);

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('invoice_type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['user_search'])) {
            $query->withUserSearch($filters['user_search']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $allowedSortFields = ['created_at', 'amount', 'status', 'due_date'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query->paginate(20);
    }


    /**
     * Update invoice status with validation, audit logging, and notifications
     *
     * @param AuctionInvoice $invoice
     * @param string $status
     * @param array $data
     * @return bool
     */

    public function updateInvoiceStatus(AuctionInvoice $invoice, string $status, array $data): bool
    {
        // Validate status transition
        if (!$invoice->canUpdateStatus($status)) {
            Log::warning('Invalid status transition attempted', [
                'invoice_id' => $invoice->id,
                'current_status' => $invoice->status,
                'attempted_status' => $status,
                'admin_id' => $data['admin_id'] ?? null,
            ]);
            return false;
        }

        // Additional validation for paid status
        if ($status === 'paid') {
            if (empty($data['payment_method']) || empty($data['transaction_id'])) {
                Log::warning('Payment confirmation details missing for paid status', [
                    'invoice_id' => $invoice->id,
                    'admin_id' => $data['admin_id'] ?? null,
                ]);
                return false;
            }
        }

        return DB::transaction(function () use ($invoice, $status, $data) {
            $oldStatus = $invoice->status;

            // Prepare update data
            $updateData = [
                'status' => $status,
                'notes' => $data['notes'] ?? null,
            ];

            if ($status === 'paid') {
                $updateData['paid_at'] = now();

                // Create or update payment record if marking as paid
                if (!empty($data['payment_method']) && !empty($data['transaction_id'])) {
                    $paymentData = [
                        'method' => $data['payment_method'],
                        'transaction_id' => $data['transaction_id'],
                        'amount' => $invoice->amount,
                        'status' => 'completed',
                        'paid_at' => now(),
                    ];

                    if ($invoice->payment) {
                        $invoice->payment->update($paymentData);
                    } else {
                        $payment = $invoice->payment()->create($paymentData);
                        $updateData['payment_id'] = $payment->id;
                    }
                }
                // Dispatch event
                event(new \App\Events\AuctionInvoicePaid($invoice));
            }


            // Update invoice
            $invoice->update($updateData);

            // Create comprehensive audit log
            $this->createAuditLog($invoice, $oldStatus, $status, $data);

            // Send appropriate notifications
            $this->sendStatusChangeNotification($invoice, $oldStatus, $status, $data);

            Log::info('Invoice status updated successfully', [
                'invoice_id' => $invoice->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'admin_id' => $data['admin_id'] ?? null,
            ]);

            return true;
        });
    }

    /**
     * Create comprehensive audit log for status changes
     *
     * @param AuctionInvoice $invoice
     * @param string $oldStatus
     * @param string $newStatus
     * @param array $data
     * @return void
     */
    private function createAuditLog(AuctionInvoice $invoice, string $oldStatus, string $newStatus, array $data): void
    {
        $auditDetails = [
            'invoice_id' => $invoice->id,
            'invoice_type' => $invoice->invoice_type,
            'user_id' => $invoice->user_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'amount' => $invoice->amount,
            'notes' => $data['notes'] ?? null,
            'timestamp' => now()->toISOString(),
        ];

        // Add payment details if status is paid
        if ($newStatus === 'paid') {
            $auditDetails['payment_details'] = [
                'payment_method' => $data['payment_method'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'paid_at' => now()->toISOString(),
            ];
        }

        AuctionAuditLog::create([
            'auction_room_id' => $invoice->auctionItem->auction_room_id ?? null,
            'auction_item_id' => $invoice->auction_item_id,
            'user_id' => $data['admin_id'] ?? auth()->id(),
            'action' => 'invoice_status_updated',
            'details' => $auditDetails,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
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

    /**
     * Send bulk payment reminders with comprehensive tracking
     *
     * @param array $invoiceIds
     * @param string $reminderType
     * @param string|null $customMessage
     * @param int $adminId
     * @return array
     */
    public function sendBulkPaymentReminders(array $invoiceIds, string $reminderType, ?string $customMessage, int $adminId): array
    {
        $invoices = AuctionInvoice::whereIn('id', $invoiceIds)
            ->overdue()
            ->with(['user', 'auctionItem.car'])
            ->get();

        $sentCount = 0;
        $failedCount = 0;
        $results = [];

        foreach ($invoices as $invoice) {
            try {
                $reminderSent = $this->sendIndividualPaymentReminder(
                    $invoice,
                    $reminderType,
                    $customMessage
                );

                if ($reminderSent) {
                    $sentCount++;

                    // Log reminder in payment history
                    $this->logPaymentReminderHistory($invoice, $reminderType, $adminId);

                    $results[] = [
                        'invoice_id' => $invoice->id,
                        'status' => 'sent',
                        'user_email' => $invoice->user->email ?? 'N/A',
                        'reminder_type' => $reminderType,
                    ];
                } else {
                    $failedCount++;
                    $results[] = [
                        'invoice_id' => $invoice->id,
                        'status' => 'failed',
                        'user_email' => $invoice->user->email ?? 'N/A',
                        'error' => 'Failed to send reminder',
                    ];
                }

            } catch (\Exception $e) {
                $failedCount++;
                $results[] = [
                    'invoice_id' => $invoice->id,
                    'status' => 'failed',
                    'user_email' => $invoice->user->email ?? 'N/A',
                    'error' => $e->getMessage(),
                ];

                Log::error('Failed to send payment reminder', [
                    'invoice_id' => $invoice->id,
                    'reminder_type' => $reminderType,
                    'admin_id' => $adminId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Log bulk reminder action
        $this->logBulkReminderAction($invoiceIds, $reminderType, $sentCount, $failedCount, $adminId);

        return [
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'total_processed' => count($invoices),
            'results' => $results,
        ];
    }

    /**
     * Send individual payment reminder
     *
     * @param AuctionInvoice $invoice
     * @param string $reminderType
     * @param string|null $customMessage
     * @return bool
     */
    private function sendIndividualPaymentReminder(AuctionInvoice $invoice, string $reminderType, ?string $customMessage): bool
    {
        if (!$invoice->user) {
            return false;
        }

        $notificationData = [
            'invoice_id' => $invoice->id,
            'car_name' => $invoice->auctionItem->car->title ?? 'Unknown Car',
            'amount' => $invoice->amount,
            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A',
            'days_overdue' => $invoice->getDaysOverdue(),
            'invoice_url' => route('admin.auction-invoices.show', $invoice->id),
            'currency' => get_setting('system_default_currency', 'USD'),
            'custom_message' => $customMessage,
        ];

        $reminderSent = false;

        // Send email reminder
        if (in_array($reminderType, ['email', 'both'])) {
            try {
                $invoice->user->notify(new AuctionNotification(
                    AuctionNotification::TYPE_PAYMENT_REMINDER,
                    $notificationData
                ));
                $reminderSent = true;
            } catch (\Exception $e) {
                Log::error('Failed to send email reminder', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send SMS reminder
        if (in_array($reminderType, ['sms', 'both']) && $invoice->user->phone) {
            try {
                $smsMessage = $customMessage ?:
                    "REMINDER: Invoice #{$invoice->id} is {$notificationData['days_overdue']} days overdue. " .
                    "Amount: {$notificationData['currency']} " . number_format($invoice->amount, 2) . ". " .
                    "Please pay immediately to avoid additional fees.";

                \App\Jobs\SendSmsToUser::dispatch($invoice->user->id, $smsMessage);
                $reminderSent = true;
            } catch (\Exception $e) {
                Log::error('Failed to send SMS reminder', [
                    'invoice_id' => $invoice->id,
                    'user_id' => $invoice->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $reminderSent;
    }

    /**
     * Export invoice data in specified format
     *
     * @param array $filters
     * @param string $format
     * @return array
     */
    public function exportInvoiceData(array $filters, string $format = 'csv'): array
    {
        $invoices = AuctionInvoice::with([
            'user:id,name,email',
            'auctionItem:id,car_id',
            'auctionItem.car:id,vin,model_id,brand_id,color_id,manufacture_year',
            'payment:id,method,transaction_id'
        ]);

        // Apply same filters as getFilteredInvoices
        if (!empty($filters['type'])) {
            $invoices->where('invoice_type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $invoices->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $invoices->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $invoices->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['user_search'])) {
            $invoices->withUserSearch($filters['user_search']);
        }

        $data = $invoices->get();

        if ($format === 'csv') {
            return $this->generateCsvExport($data);
        }

        // Default to CSV if format not supported
        return $this->generateCsvExport($data);
    }

    /**
     * Generate CSV export data
     *
     * @param Collection $invoices
     * @return array
     */
    private function generateCsvExport(Collection $invoices): array
    {
        $csvData = "ID,Type,User Name,User Email,Amount,Commission,Net Amount,Status,Due Date,Paid At,Car Title,Created At\n";

        foreach ($invoices as $invoice) {
            $csvData .= sprintf(
                "%d,%s,%s,%s,%.2f,%.2f,%.2f,%s,%s,%s,%s,%s\n",
                $invoice->id,
                $invoice->invoice_type,
                $invoice->user->name ?? '',
                $invoice->user->email ?? '',
                $invoice->amount,
                $invoice->commission_amount ?? 0,
                $invoice->net_amount,
                $invoice->status,
                $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '',
                $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : '',
                $invoice->auctionItem->car->title ?? '',
                $invoice->created_at->format('Y-m-d H:i:s')
            );
        }

        return [
            'content' => $csvData,
            'mime_type' => 'text/csv',
        ];
    }

    /**
     * Send comprehensive status change notification to user
     *
     * @param AuctionInvoice $invoice
     * @param string $oldStatus
     * @param string $newStatus
     * @param array $data
     * @return void
     */
    private function sendStatusChangeNotification(AuctionInvoice $invoice, string $oldStatus, string $newStatus, array $data = []): void
    {
        try {
            $invoice->load(['user', 'auctionItem.car']);

            // Determine notification type based on new status
            $notificationType = $this->getNotificationTypeForStatus($newStatus);

            // Prepare notification data
            $notificationData = [
                'invoice_id' => $invoice->id,
                'car_name' => $invoice->auctionItem->car->title ?? 'Unknown Car',
                'amount' => $invoice->amount,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'invoice_url' => route('admin.auction-invoices.show', $invoice->id),
                'currency' => get_setting('system_default_currency', 'USD'),
            ];

            // Add status-specific data
            switch ($newStatus) {
                case 'paid':
                    $notificationData['payment_method'] = $data['payment_method'] ?? 'Unknown';
                    $notificationData['transaction_id'] = $data['transaction_id'] ?? 'N/A';
                    break;

                case 'overdue':
                    $notificationData['due_date'] = $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'N/A';
                    $notificationData['days_overdue'] = $invoice->due_date ? now()->diffInDays($invoice->due_date) : 0;
                    break;

                case 'cancelled':
                    $notificationData['reason'] = $data['notes'] ?? 'No reason provided';
                    break;
            }

            // Add notes if provided
            if (!empty($data['notes'])) {
                $notificationData['notes'] = $data['notes'];
            }

            // Send notification to the invoice user
            if ($invoice->user) {
                $invoice->user->notify(new AuctionNotification(
                    $notificationType,
                    $notificationData
                ));

                // Send SMS for critical status changes
                if ($this->shouldSendSmsForStatus($newStatus) && $invoice->user->phone) {
                    \App\Jobs\SendSmsToUser::dispatch(
                        $invoice->user->id,
                        $this->getSmsMessageForStatus($newStatus, $notificationData)
                    );
                }
            }

            Log::info('Status change notification sent', [
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'notification_type' => $notificationType,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send status change notification', [
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get notification type for status change
     *
     * @param string $status
     * @return string
     */
    private function getNotificationTypeForStatus(string $status): string
    {
        switch ($status) {
            case 'paid':
                return AuctionNotification::TYPE_PAYMENT_CONFIRMED;
            case 'overdue':
                return AuctionNotification::TYPE_INVOICE_OVERDUE;
            case 'cancelled':
                return AuctionNotification::TYPE_INVOICE_CANCELLED;
            default:
                return AuctionNotification::TYPE_INVOICE_STATUS_CHANGED;
        }
    }

    /**
     * Determine if SMS should be sent for status change
     *
     * @param string $status
     * @return bool
     */
    private function shouldSendSmsForStatus(string $status): bool
    {
        return in_array($status, ['paid', 'overdue']);
    }

    /**
     * Get SMS message for status change
     *
     * @param string $status
     * @param array $data
     * @return string
     */
    private function getSmsMessageForStatus(string $status, array $data): string
    {
        switch ($status) {
            case 'paid':
                return "Payment confirmed! {$data['currency']} " . number_format($data['amount'], 2) . " received for {$data['car_name']}.";
            case 'overdue':
                return "URGENT: Invoice #{$data['invoice_id']} is overdue. Amount: {$data['currency']} " . number_format($data['amount'], 2) . ". Please pay immediately.";
            default:
                return "Invoice #{$data['invoice_id']} status updated to {$data['new_status']}.";
        }
    }

    /**
     * Log payment reminder history
     *
     * @param AuctionInvoice $invoice
     * @param string $reminderType
     * @param int $adminId
     * @return void
     */
    private function logPaymentReminderHistory(AuctionInvoice $invoice, string $reminderType, int $adminId): void
    {
        AuctionAuditLog::create([
            'auction_room_id' => $invoice->auctionItem->auction_room_id ?? null,
            'auction_item_id' => $invoice->auction_item_id,
            'user_id' => $adminId,
            'action' => 'payment_reminder_sent',
            'details' => [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,
                'user_id' => $invoice->user_id,
                'reminder_type' => $reminderType,
                'days_overdue' => $invoice->getDaysOverdue(),
                'amount' => $invoice->amount,
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log bulk reminder action
     *
     * @param array $invoiceIds
     * @param string $reminderType
     * @param int $sentCount
     * @param int $failedCount
     * @param int $adminId
     * @return void
     */
    private function logBulkReminderAction(array $invoiceIds, string $reminderType, int $sentCount, int $failedCount, int $adminId): void
    {
        AuctionAuditLog::create([
            'auction_room_id' => null,
            'auction_item_id' => null,
            'user_id' => $adminId,
            'action' => 'bulk_payment_reminders',
            'details' => [
                'invoice_ids' => $invoiceIds,
                'reminder_type' => $reminderType,
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_invoices' => count($invoiceIds),
                'timestamp' => now()->toISOString(),
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
