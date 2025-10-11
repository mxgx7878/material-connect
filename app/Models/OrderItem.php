<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'quantity', 'supplier_id', 'price'
    ];

    /**
     * Relationship to the order
     */
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    /**
     * Relationship to the product
     */
    public function product()
    {
        return $this->belongsTo(MasterProducts::class, 'product_id');
    }

    /**
     * Relationship to the supplier
     */
    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }
}
