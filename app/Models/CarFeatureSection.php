<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarFeatureSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
    // relationships

    /**
     * Get the features in this section
     */
    public function features()
    {
        return $this->hasMany(CarFeature::class, 'section_id');
    }


}
