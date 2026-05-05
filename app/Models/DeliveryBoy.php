<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryBoy extends Model
{
    public function user(){
    	return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable')->where('status', 1);
    }

     public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

     public function getRatingCountAttribute()
    {
        return $this->reviews()->count();
    }
}
