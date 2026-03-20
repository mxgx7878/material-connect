<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemDelivery extends Model
{
    protected $table = 'order_item_deliveries';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'supplier_id',
        'quantity',
        'delivery_date',
        'delivery_time',
        'delivery_cost',
        'truck_type',
        'load_size',
        'time_interval',
        'supplier_confirms',
        'status',
        'invoice_id',
        'accelerator_type',
        'retarder_type',
        'aggregate_size',
        'slump_value',
        'oxide_fibre',
        'paver_delivery',
        'omc_conditioning',
        'additional_stabiliser',
    ];

    public const DELIVERY_STATUS = ['Pending', 'Scheduled', 'Delivered', 'Cancelled', 'On Hold'];

    protected $casts = [
        'quantity'               => 'decimal:2',
        'delivery_date'          => 'date',
        'delivery_time'          => 'datetime:H:i:s',
        'supplier_confirms'      => 'boolean',
        'delivery_cost'          => 'decimal:2',
        'oxide_fibre'            => 'boolean',
        'paver_delivery'         => 'boolean',
        'omc_conditioning'       => 'boolean',
        'additional_stabiliser'  => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    public function surcharges()
    {
        return $this->hasMany(\App\Models\OrderItemDeliverySurcharge::class, 'order_item_delivery_id');
    }
}
