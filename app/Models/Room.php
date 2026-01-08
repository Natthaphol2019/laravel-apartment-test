<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    //

    protected $fillable = [
        'room_number',
        'room_price_id',
        'status',
    ];

    public function roomPrice()
    {
        return $this->belongsTo(RoomPrices::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
}
