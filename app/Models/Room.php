<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    //

    protected $fillable = [
        'room_number',
        'building_id',
        'room_type_id',
        'status',
        //จัดการ planfloor
        'pos_x',
        'pos_y',
        'width',
        'height'
    ];

    public function roomPrice()
    {
        return $this->belongsTo(RoomPrices::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }
    //8:29 1/9/2026 เพิ่มส่วนนี้เข้าไปเพื่อดึงชั้นมา
    public function getFloorAttribute()
    {
        return substr($this->room_number, 0, 1);
    }
}
