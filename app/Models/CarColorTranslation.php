<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarColorTranslation extends Model
{
    use HasFactory;

     protected $fillable = [
        'lang',
        'car_color_id',
        'name',
    ];

    public function carColor()
    {
        return $this->belongsTo(CarColor::class, 'car_color_id');
    }
}
