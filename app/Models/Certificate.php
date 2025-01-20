<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_name',
        'product_description',
        'certificate_reg_no',
        'issue_no',
        'initial_approval',
        'next_surveillance',
        'date_of_issue',
        'valid_until',
        'certificate_file',
        'status',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
