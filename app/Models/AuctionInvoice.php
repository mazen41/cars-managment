<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_item_id',
        'invoice_type',
        'user_id',
        'amount',
        'commission_amount',
        'net_amount',
        'notes',
        'payment_id',
        'status',
        'due_date',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the auction item this invoice is for.
     */
    public function auctionItem(): BelongsTo
    {
        return $this->belongsTo(AuctionItem::class);
    }

    /**
     * Get the user this invoice belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment record for this invoice.
     */
    public function payment()
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    /** Get the commission record for this invoice. */
    public function commission()
    {
        return $this->morphOne(Commission::class, 'commissionable');
    }

    /**
     * Scope a query to only include buyer invoices.
     */
    public function scopeBuyer($query)
    {
        return $query->where('invoice_type', 'buyer_payment');
    }

    /**
     * Scope a query to only include seller invoices.
     */
    public function scopeSeller($query)
    {
        return $query->where('invoice_type', 'seller_payout');
    }

    /**
     * Scope a query to only include paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending invoices.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if the invoice has been paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Mark the invoice as paid.
     */
    public function markAsPaid(Payment $payment): void
    {
        $this->status = 'paid';
        $this->payment_id = $payment->id;
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Check if this is a buyer invoice.
     */
    public function isBuyerInvoice(): bool
    {
        return $this->invoice_type === 'buyer_payment';
    }

    /**
     * Check if this is a seller payout invoice.
     */
    public function isSellerPayout(): bool
    {
        return $this->invoice_type === 'seller_payout';
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->where('due_date', '<', now())
                     ->whereNotNull('due_date');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to search by user information.
     */
    public function scopeWithUserSearch($query, $searchTerm)
    {
        return $query->whereHas('user', function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('email', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Get formatted amount attribute.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get status badge attribute for display.
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'badge-warning',
            'paid' => 'badge-success',
            'overdue' => 'badge-danger',
            'cancelled' => 'badge-secondary',
            'disputed' => 'badge-info',
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    /**
     * Check if the invoice status can be updated to the new status.
     */
    public function canUpdateStatus(string $newStatus): bool
    {
        // Cannot update to the same status
        if ($this->status === $newStatus) {
            return false;
        }

        $allowedTransitions = [
            'pending' => ['paid', 'overdue', 'cancelled', 'disputed'],
            'overdue' => ['paid', 'cancelled', 'disputed'],
            'disputed' => ['paid', 'cancelled', 'pending'],
            'paid' => [], // Paid invoices cannot be changed
            'cancelled' => [], // Cancelled invoices cannot be changed
        ];

        return in_array($newStatus, $allowedTransitions[$this->status] ?? []);
    }

    /**
     * Get all valid status transitions for current status.
     */
    public function getValidStatusTransitions(): array
    {
        $transitions = [
            'pending' => [
                'paid' => 'Mark as Paid',
                'overdue' => 'Mark as Overdue',
                'cancelled' => 'Cancel Invoice',
                'disputed' => 'Mark as Disputed'
            ],
            'overdue' => [
                'paid' => 'Mark as Paid',
                'cancelled' => 'Cancel Invoice',
                'disputed' => 'Mark as Disputed'
            ],
            'disputed' => [
                'paid' => 'Mark as Paid',
                'cancelled' => 'Cancel Invoice',
                'pending' => 'Return to Pending'
            ],
            'paid' => [],
            'cancelled' => [],
        ];

        return $transitions[$this->status] ?? [];
    }

    /**
     * Check if invoice requires payment confirmation details.
     */
    public function requiresPaymentConfirmation(string $newStatus): bool
    {
        return $newStatus === 'paid';
    }

    /**
     * Get status validation rules for updates.
     */
    public static function getStatusValidationRules(): array
    {
        return [
            'status' => 'required|in:pending,paid,overdue,cancelled,disputed',
            'payment_method' => 'nullable|required_if:status,paid|string|max:255',
            'transaction_id' => 'nullable|required_if:status,paid|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending'
            && $this->due_date
            && $this->due_date->isPast();
    }

    /**
     * Get days overdue (returns 0 if not overdue).
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    /**
     * Check if invoice can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->status !== 'paid' && $this->status !== 'cancelled';
    }

}
