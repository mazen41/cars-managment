<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_order_id',
        'amount',
        'status',
        'reason',
        'refund_reference',
        'payment_method',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2'
    ];


    public function externalOrder(): BelongsTo
    {
        return $this->belongsTo(ExternalOrder::class);
    }
}