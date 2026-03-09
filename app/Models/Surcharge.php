<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Surcharge extends Model
{
    use SoftDeletes;

    protected $table = 'surcharges';

    protected $fillable = [
        'name',
        'description',
        'conditions',
        'worked_example',
        'billing_code',
        'amount',
        'amount_type',
        'applies_to',
        'is_active',
        'integrated',
        'sort_order',
    ];

    protected $casts = [
        'amount'     => 'float',
        'is_active'  => 'boolean',
        'integrated' => 'boolean',
        'sort_order' => 'integer',
    ];
}