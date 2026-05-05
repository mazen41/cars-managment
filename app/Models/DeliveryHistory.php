<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryHistory extends Model
{
    public function orderable()
    {
        return $this->morphTo();
    }
}
