<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasManyThrough};

class Orders extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'po_number',
        'client_id',
        'project_id',
        'delivery_address',
        'delivery_lat',
        'delivery_long',
        'delivery_date',
        'delivery_time',
        'delivery_window',
        'delivery_method',
        'load_size',
        'special_equipment',
        'subtotal',
        'fuel_levy',
        'other_charges',
        'gst_tax',
        'discount',
        'total_price',
        'supplier_cost',
        'customer_cost',
        'customer_item_cost',
        'customer_delivery_cost',
        'payment_status',
        'order_status',
        'reason',
        'repeat_order',
        'generate_invoice',
        'special_notes',
        'supplier_paid_ids',
        'contact_person_name',
        'contact_person_number',
        'is_archived',
        'archived_by',
        'supplier_item_cost',
        'supplier_delivery_cost',
        'requires_testing',
        'customer_confirmed',
        'customer_confirmed_at',
    ];

    protected $casts = [
        'delivery_date'          => 'datetime',
        'delivery_time'          => 'datetime:H:i:s',
        'delivery_lat'           => 'float',
        'delivery_long'          => 'float',
        'subtotal'               => 'decimal:2',
        'fuel_levy'              => 'decimal:2',
        'other_charges'          => 'decimal:2',
        'gst_tax'                => 'decimal:2',
        'discount'               => 'decimal:2',
        'total_price'            => 'decimal:2',
        'supplier_cost'          => 'decimal:2',
        'customer_cost'          => 'decimal:2',
        'customer_item_cost'     => 'decimal:2',
        'customer_delivery_cost' => 'decimal:2',
        'supplier_item_cost'     => 'decimal:2',
        'supplier_delivery_cost' => 'decimal:2',
        'profit_amount'          => 'decimal:2',
        'profit_margin_percent'  => 'decimal:2',
        'admin_margin'           => 'decimal:2',
        'repeat_order'           => 'boolean',
        'generate_invoice'       => 'boolean',
        'is_archived'            => 'boolean',
        'archived_by'            => 'integer',
    ];

    // Canonical status list now lives in OrderStatusService::all().
    public const PAYMENT_STATUS  = ['Pending','Paid','Partially Paid','Partial Refunded','Refunded','Requested'];
    public const STATUS = [
        'Received', 'Under Review', 'Confirming Supply',
        'Awaiting Customer Confirmation', 'Processing', 'Completed',
        // edge
        'Cancelled', 'Supplier Unavailable', 'Customer Action Required',
    ];
    public const DELIVERY_WINDOW = ['Morning','Afternoon','Evening'];
    public const DELIVERY_METHOD = ['Other','Tipper','Agitator','Pump','Ute'];

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function suppliers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            OrderItem::class,
            'order_id',
            'id',
            'id',
            'supplier_id'
        )->whereNotNull('order_items.supplier_id')->distinct();
    }

    public function itemDeliveries(): HasMany
    {
        return $this->hasMany(OrderItemDelivery::class, 'order_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'order_id');
    }

    public function scopeForClient($q, int $clientId) { return $q->where('client_id', $clientId); }
    public function scopeStatus($q, string $status)   { return $q->where('order_status', $status); }
}