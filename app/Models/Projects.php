<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;


class Projects extends Model
{
    use HasFactory;

    

    // Define the table associated with the model
    protected $table = 'projects';

    // Define the fields that are mass assignable
    protected $fillable = [
        'name',
        'site_contact_name',
        'site_contact_phone',
        'site_instructions',
        'delivery_address',
        'delivery_lat',
        'delivery_long',
        'added_by',
        'is_archived',
        'archived_by'
    ];

    // Define the relationship to the User model
    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by')->select('id','name','email','profile_image');  // 'added_by' is the foreign key
    }

    public function archived_by()
    {
        return $this->belongsTo(User::class, 'archived_by')->select('id','name','email','profile_image');  // 'archived_by' is the foreign key
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Orders::class, 'project_id');
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,   // final model
            Orders::class,    // intermediate model
            'project_id',     // FK on orders  → projects.id
            'order_id',       // FK on invoices → orders.id
            'id',             // local key on projects
            'id'              // local key on orders
        );
    }
}
