<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    //
    protected $fillable = [
        'invoice_id',
        'tenant_expense_id',
        'meter_reading_id', 
        'name',
        'previous_unit',
        'current_unit', 
        'quantity',
        'price_per_unit',
        'subtotal'
    ];

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function meterReading() {
        return $this->belongsTo(MeterReading::class);
    }

}
