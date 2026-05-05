<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProductTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_product_id',
        'lang',
        'name',
        'description',
    ];

    public function customerProduct(): BelongsTo
    {
        return $this->belongsTo(CustomerProduct::class);
    }
}
