<?php
// FILE PATH: app/Models/FailedInvoiceLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FailedInvoiceLog extends Model
{
    protected $table = 'failed_invoice_logs';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'order_item_delivery_id',
        'reason',
        'run_date',
    ];

    protected $casts = [
        'run_date' => 'date',
    ];

    // ── Relations ──

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
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