<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    public function user(){
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function shop()
    {
        return $this->hasOneThrough(
            Shop::class,
            User::class,
            'id',
            'user_id',
            'seller_id',
            'id'
        );
    }
}
