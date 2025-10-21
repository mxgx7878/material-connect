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
        'payment_status',
        'order_status',
        'workflow',
        'reason',
        'repeat_order',
        'generate_invoice',
        'order_process',
        'special_notes',
        'supplier_paid_ids',
    ];

    protected $casts = [
        'delivery_date'     => 'datetime',
        'delivery_time'     => 'datetime:H:i:s',
        'delivery_lat'      => 'float',
        'delivery_long'     => 'float',
        'subtotal'          => 'decimal:2',
        'fuel_levy'         => 'decimal:2',
        'other_charges'     => 'decimal:2',
        'gst_tax'           => 'decimal:2',
        'discount'          => 'decimal:2',
        'total_price'       => 'decimal:2',
        'supplier_cost'     => 'decimal:2',
        'customer_cost'     => 'decimal:2',
        'admin_margin'      => 'decimal:2',
        'repeat_order'      => 'boolean',
        'generate_invoice'  => 'boolean',
    ];

    // Enums as constants (optional helpers)
    public const PAYMENT_STATUS = ['Pending','Paid','Partially Paid','Partial Refunded','Refunded','Requested'];
    public const ORDER_STATUS   = ['In-Progress','Completed','Cancelled'];
    public const DELIVERY_WINDOW = ['Morning','Afternoon','Evening'];
    public const DELIVERY_METHOD = ['Other','Tipper','Agitator','Pump','Ute'];
    public const ORDER_PROCESS   = ['Automated','Action Required'];
    public const WORKFLOW        = ['Requested','Supplier Missing','Supplier Assigned','Payment Requested','On Hold','Delivered'];

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

    // Convenience: unique suppliers on this order
    public function suppliers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            OrderItem::class,
            'order_id',   // OrderItem foreign key...
            'id',         // User local key
            'id',         // Order local key
            'supplier_id' // OrderItem supplier key
        )->whereNotNull('order_items.supplier_id')->distinct();
    }

    // Scopes
    public function scopeForClient($q, int $clientId) { return $q->where('client_id', $clientId); }
    public function scopeStatus($q, string $status)   { return $q->where('order_status', $status); }
}
