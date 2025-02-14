<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model ;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword; // Use the HasApiTokens trait here

    protected $table = 'admins'; // Specify your table

    // Add fillable properties to allow mass assignment
    protected $fillable = [
        'name',
        'email',
        'password',
        'contact_no',
        'image',
        'role',
         'address',
    ];

}
