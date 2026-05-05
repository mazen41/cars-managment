<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'sender_viewed',
        'receiver_viewed',
        'title',
        'conversable_id',
        'conversable_type'
    ];

    protected $casts = [
    'sender_id' => 'integer',
    'receiver_id' => 'integer',
];

    public function messages(){
        return $this->hasMany(Message::class);
    }

    public function sender(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(){
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function conversable()
    {
        return $this->morphTo();
    }
}
