<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class UserLogin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user_login';

    protected $fillable = [
        'name',
        'email',
        'password',
        'contact_no',
        'image',
        'role',
        'category',
        'ba_name',
    ];
    public function clientProfiles()
    {
        return $this->hasMany(ClientProfile::class, 'email');
    }
}

