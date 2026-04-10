<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemDeliveryTestingFee extends Model
{
    protected $table = 'order_item_delivery_testing_fees';

    protected $fillable = [
        'order_item_delivery_id',
        'testing_fee_id',
        'amount_snapshot',
    ];

    protected $casts = [
        'amount_snapshot' => 'decimal:2',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(OrderItemDelivery::class, 'order_item_delivery_id');
    }

    public function testingFee(): BelongsTo
    {
        return $this->belongsTo(TestingFee::class, 'testing_fee_id');
    }
}