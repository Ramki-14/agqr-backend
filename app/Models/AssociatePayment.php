<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociatePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        
        'associate_id',
        'associate_company',
        'associate_name',
        'principal_amount',
        'returned_amount',
        'outstanding_amount',
    ];
}
