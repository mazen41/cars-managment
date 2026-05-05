<?php

namespace App\Models;

use App\Traits\HasTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarColor extends Model
{
    use HasFactory, HasTranslation;

    protected $fillable = ["name", "hex_code", "status"];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        "status" => "string",
    ];


    /**
     * Check if the color can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->cars()->count() === 0;
    }

    /**
     * Scope a query to only include active colors.
     */
    public function scopeActive($query)
    {
        return $query->where("status", "active");
    }

    /**
     * Scope a query to only include inactive colors.
     */
    public function scopeInactive($query)
    {
        return $query->where("status", "inactive");
    }
    /**
     * Get the cars associated with this color.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Car, CarColor>
     */
    public function cars()
    {
        return $this->hasMany(Car::class, "color_id");
    }
    /**
     * Get the translations for this car color.
     */
    public function translations()
    {
        return $this->hasMany(CarColorTranslation::class, "car_color_id");
    }
}
