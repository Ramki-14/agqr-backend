<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'order_id',
        'client_name',
        'product_name',
        'product_description',
        'rate',
        'sgst',
        'cgst',
        'igst',
        'payable_amount',
        'received_amount',
        'balance',
        'payment_method',
        'payment_date',
    ];
    
}
