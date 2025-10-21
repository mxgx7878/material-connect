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
        'notes',
        'delivery_zones',
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'isDeleted' => 'boolean',
        'lat' => 'float',
        'long' => 'float',
        'delivery_zones' => 'array',
    ];


    // If it's stored as TEXT and not JSON type, use accessor:
    public function getDeliveryZonesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function projects()
    {
        return $this->hasMany(Projects::class, 'added_by');  // 'added_by' is the foreign key
    }

    public function supplierOffers()
    {
        return $this->hasMany(SupplierOffers::class, 'supplier_id'); // 'supplier_id' is the foreign key in SupplierOffer
    }
}
