<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloosakWallet extends Model
{
    use HasFactory;

    protected $fillable =['wallet_id', 'currency_id','currency_name', 'currency_symbol'];

}
