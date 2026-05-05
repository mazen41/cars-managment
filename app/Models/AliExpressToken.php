<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AliExpressToken extends Model
{
    use HasFactory;
    protected $table='aliexpress_tokens';
    protected $fillable = [
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'test_date'
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime'
    ];

    public function hasValidAccessToken()
    {
        return $this->access_token && $this->access_token_expires_at && $this->access_token_expires_at > now();
    }

    public function hasValidRefreshToken()
    {
        return $this->refresh_token && $this->refresh_token_expires_at && $this->refresh_token_expires_at > now();
    }

}
