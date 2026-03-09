<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemDeliverySurcharge extends Model
{
    protected $table = 'order_item_delivery_surcharges';

    protected $fillable = [
        'order_item_delivery_id',
        'surcharge_id',
        'amount_snapshot',
        'calculated_amount',
        'is_auto',
        'notes',
    ];

    protected $casts = [
        'amount_snapshot'   => 'float',
        'calculated_amount' => 'float',
        'is_auto'           => 'boolean',
    ];

    public function delivery()
    {
        return $this->belongsTo(OrderItemDelivery::class, 'order_item_delivery_id');
    }

    public function surcharge()
    {
        return $this->belongsTo(Surcharge::class, 'surcharge_id');
    }
}