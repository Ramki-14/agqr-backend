<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociateClientOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_name',
        'product_description',
        'sgst_amount',
        'cgst_amount',
        'igst_amount',
        'rate',
        'gst_amount',
        'total_amount',
        'balance_amount',
        'audit_type',
        'status',
        'associate_company',
        'associate_name',
        'associate_id',
    ];
}
