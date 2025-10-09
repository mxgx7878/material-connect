<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'project_id',  // Add project_id to the fillable attributes
        'delivery_method',
        'delivery_address',
        'delivery_date',
        'subtotal',
        'tax',
        'fuel_levy',
        'total_price',
        'margin',
        'supplier_cost',
        'customer_cost',
        'discount',
        'repeat_order',
        'custom_mix_blend',
    ];

    /**
     * Relationship to the client
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relationship to the project
     */
    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    /**
     * Relationship to order items (products)
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
