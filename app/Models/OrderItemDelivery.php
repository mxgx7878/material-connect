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
        'supplier_confirms',
        'status',
    ];

    public const DELIVERY_STATUS = ['Pending', 'Scheduled', 'Delivered', 'Cancelled', 'On Hold'];

    protected $casts = [
        'quantity'          => 'decimal:2',
        'delivery_date'     => 'date',
        'delivery_time'     => 'datetime:H:i:s', // or keep as string/time; Laravel usually treats TIME as string
        'supplier_confirms' => 'boolean',
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
}
