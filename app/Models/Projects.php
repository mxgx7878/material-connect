<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


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
        'added_by',
    ];

    // Define the relationship to the User model
    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');  // 'added_by' is the foreign key
    }
}
