<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'type','name','company','email','phone','contact_method','subject','message',
        'product_id','suburb','postcode','lat','lng','payload','files','source',
        'status','ip_address','user_agent',
    ];

    protected $casts = [
        'payload' => 'array',
        'files'   => 'array',
    ];
}