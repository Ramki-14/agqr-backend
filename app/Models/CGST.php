<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CGST extends Model
{
    use HasFactory;
    protected $table = 'cgst';
    protected $fillable = ['rate'];
}
