<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociatePaymentHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'associate_id',
        'associate_name',
        'associate_company',
        'client_id',
        'client_name',
        'product_name',
        'total_amount',
        'description',
    ];
}
