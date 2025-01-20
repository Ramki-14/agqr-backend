<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociateClientCertificate extends Model
{
    use HasFactory;
    protected $table = 'associate_client_certificate'; // Table name

    protected $fillable = [
        'order_id',
        'associate_id',
        'associate_name',
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
        return $this->belongsTo(AssociateClientOrder::class, 'order_id');
    }

    public function associate()
    {
        return $this->belongsTo(AssociativeLogin::class, 'associate_id');
    }

}
