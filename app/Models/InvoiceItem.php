<?php
// FILE PATH: app/Models/InvoiceItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'order_item_id',
        'order_item_delivery_id',
        'product_name',
        'quantity',
        'unit_price',
        'delivery_cost',
        'line_total',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'line_total'    => 'decimal:2',
    ];

    // ── Relations ──

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(OrderItemDelivery::class, 'order_item_delivery_id');
    }
}