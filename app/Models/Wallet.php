<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Sortable;

class Wallet extends Model
{
    use Sortable;
    protected $fillable = [
        'user_id',
        'amount',
        'offline_payment',
        'payment_method',
        'approval',
        'payment_details'
    ];
    protected $casts = [
        'payment_details' => 'object'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeApproved($query)
    {
        return $query->where(function ($query) {
            $query->where('offline_payment', false)
                ->orWhere(function ($q) {
                    $q->where('offline_payment', true)
                        ->where('approval', true);
                });
        });
    }
}
