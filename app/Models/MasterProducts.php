<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProducts extends Model
{
    protected $table = 'master_products';
    protected $fillable = ['product_name', 'product_type', 'specifications', 'unit_of_measure', 'tech_doc', 'photo','is_approved','added_by','approved_by', 'slug', 'category'];

    public function supplierOffers()
    {
        return $this->hasMany(SupplierOffers::class, 'master_product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category')->select('id', 'name');
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by')->select('id', 'name', 'email','profile_image');
    }

    // Relationship with the user who approved the product
    public function approved_by()
    {
        return $this->belongsTo(User::class, 'approved_by')->select('id', 'name', 'email','profile_image');
    }
}
