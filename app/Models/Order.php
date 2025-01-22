<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_name',
        'product_description',
        'audit_type',
        'invoice_number',
        'sgst_amount',
        'cgst_amount',
        'igst_amount',
        'rate',
        'gst_amount',
        'total_amount',
        'balance_amount',
        'status',
    ];

    public function clientProfile()
{
    return $this->belongsTo(ClientProfile::class, 'client_id', 'client_id');
}
}
