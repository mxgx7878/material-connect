<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};


class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'supplier_id',
        'custom_blend_mix',
        'supplier_unit_cost',
        'supplier_delivery_cost',
        'supplier_discount',
        'supplier_delivery_date',
        'choosen_offer_id',
        'suppier_notes',
        'supplier_confirms',
        'delivery_type',
        'delivery_cost',
        'is_qouted',
        'quoted_price',
        'is_paid',
    ];

    protected $casts = [
        'quantity'               => 'decimal:2',
        'supplier_unit_cost'     => 'decimal:2',
        'supplier_delivery_cost' => 'decimal:2',
        'supplier_discount'      => 'decimal:2',
        'quoted_price'           => 'decimal:2',
        'delivery_cost'          => 'decimal:2',
        'supplier_confirms'      => 'boolean',
        'is_paid'                => 'boolean',
        'is_qouted'              => 'boolean',
        'supplier_delivery_date' => 'datetime',

    ];

    // Relations
    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MasterProducts::class, 'product_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function chosenOffer(): BelongsTo
    {
        return $this->belongsTo(SupplierOffers::class, 'choosen_offer_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(OrderItemDelivery::class, 'order_item_id');
    }
}
