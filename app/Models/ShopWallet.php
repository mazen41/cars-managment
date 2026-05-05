<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopWallet extends Model
{
    use HasFactory;
    protected $fillable = [
        'wallet_name',
        'account_holder_name',
        'account_number',
        'is_active'
    ];

    public function shop(){
        return $this->belongsTo(Shop::class, 'seller_id');
    }
}
