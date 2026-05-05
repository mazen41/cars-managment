<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Commission extends Model
{
    use HasFactory;
    protected $fillable = [
        'admin_commission',
        'ownable_earning',
        'ownable_type',
        'ownable_id',
        'commissionable_type',
        'commissionable_id',
    ];
    public function ownable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commissionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCommissionableNameAttribute(): string
    {
        return match($this->commissionable_type) {
            'App\Models\Order' => translate('Order'),
            'App\Models\CarInspection' => translate('Car Inspection'),
            'App\Models\CarReservation' => translate('Car Reservation'),
            'App\Models\AuctionInvoice' => translate('Auction Invoice'),
            default => class_basename($this->commissionable_type),
        };
    }

    // get reference code
    public function getReferenceCodeAttribute(): string
    {
        return match($this->commissionable_type) {
            'App\Models\Order' => $this->commissionable->code ?? translate('Order Deleted'),
            'App\Models\CarInspection' => $this->commissionable->inspection_number ?? translate('Inspection Deleted'),
            'App\Models\CarReservation' => $this->commissionable->reservation_id ?? translate('Reservation Deleted'),
            'App\Models\AuctionInvoice' => $this->commissionable->id ?? translate('Invoice Deleted'),
            default => translate('Reference Deleted'),
        };
    }
}
