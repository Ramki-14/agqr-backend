<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SGST extends Model
{
    use HasFactory;

    protected $table = 'sgst';
    
    protected $fillable = ['rate'];
}
