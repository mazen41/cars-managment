<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
  public function user(){
    return $this->belongsTo(User::class);
  }

  public function product(){
    return $this->belongsTo(Product::class, 'reviewable_id')->where('reviewable_type', Product::class);
  }

    public function reviewable(){
        return $this->morphTo();
    }
}
