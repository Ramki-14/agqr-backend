<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociateClient extends Model
{
    use HasFactory;

    protected $table = 'associate_client'; // Specify the table name if it differs from the default
    protected $fillable = [
        'account_type',
        'client_name',
        'address',
        'client_gst_no',
        'company_name',
        'client_gst_document', 
        'gst_number',
        'associate_name',
    ];
}
