<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestingFee extends Model
{
    use SoftDeletes;

    protected $table = 'testing_fees';

    protected $fillable = [
        'name',
        'description',
        'conditions',
        'worked_example',
        'billing_code',
        'amount',
        'amount_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount'     => 'float',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];
}