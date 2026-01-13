<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Room;

class Tenant extends Authenticatable
{
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
        'rental_contract',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'start_date' => 'date',
            'end_date' => 'date',
            'has_parking' => 'boolean',
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
