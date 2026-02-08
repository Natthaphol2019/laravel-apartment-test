<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Tenant extends Authenticatable
{
    //
    protected $fillable = [
        'room_id',
        'id_card',
        'password',
        'first_name',
        'last_name',
        'address_no',
        'moo',
        'sub_district',
        'district',
        'province',
        'postal_code',
        'phone',
        'start_date',
        'end_date',
        'has_parking',
        'resident_count',
        'deposit_amount',
        'rental_contract',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
        protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
