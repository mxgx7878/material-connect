<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
        'contact_name',
        'contact_number',
        'location',
        'lat',
        'long',
        'delivery_radius',
        'shipping_address',
        'billing_address',
        'client_public_id',
        'profile_image',
        'isDeleted',
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'isDeleted' => 'boolean',
        'lat' => 'float',
        'long' => 'float',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'added_by');  // 'added_by' is the foreign key
    }
}
