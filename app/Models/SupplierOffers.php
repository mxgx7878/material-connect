<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierOffers extends Model
{
    protected $table = 'supplier_offers';
    protected $fillable = ['supplier_id', 'master_product_id', 'price', 'availability_status', 'status'];

    public function masterProduct()
    {
        return $this->belongsTo(MasterProducts::class);
    }

    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id')
            ->select('id', 'name', 'email', 'profile_image', 'delivery_zones')
            ->withCasts(['delivery_zones' => 'array']);
    }
}
