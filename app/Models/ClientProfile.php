<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    use HasFactory;

    protected $primaryKey = 'client_id'; // Set primary key as client_id
  
    protected $fillable = [
      
        'user_login_id',
        'client_name',
        'contact_person',
        'email', // Foreign key for user login
        'contact_no',
        'address',
        'category',
        'gst_number',
        'Audit_type',
        'status',
        'image',
        'gst_document',
        'notes',
    ];

    // Define the relationship to UserLogin model
    public function user()
    {
        return $this->belongsTo(UserLogin::class, 'user_login_id'); // Assumes UserLogin model is in App\Models\UserLogin
    }
    public function orders()
{
    return $this->hasMany(Order::class, 'client_id', 'client_id');
}
}

