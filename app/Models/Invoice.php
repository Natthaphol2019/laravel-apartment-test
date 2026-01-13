<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //
    protected $fillable = [
        'tenant_id',
        'room_id',
        'invoice_number', 
        'billing_month',
        'issue_date',
        'total_amount',
        'status',
        'due_date'
    ];

    // ดึงรายการย่อยของใบแจ้งหนี้
    public function details() {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }
}
