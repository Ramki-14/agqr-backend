<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociatePaymentReceipt extends Model
{
    use HasFactory;

    protected $table = 'associate_payment_receipt';

    protected $fillable = [
        'associate_id',
        'associate_name',
        'associate_company',
        'received_amount',
        'received_date',
        'received_method',
    ];
}
