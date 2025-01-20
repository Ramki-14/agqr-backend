<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class AssociativeLogin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable , CanResetPassword;

    protected $table = 'associative_login';

    protected $fillable = [
        'company_name',
        'name',
        'email',
        'password',
        'contact_no',
        'gst_number',
        'account_type',
        'image',
        'role',
    ];
}
