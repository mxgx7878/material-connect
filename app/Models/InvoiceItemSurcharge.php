<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItemSurcharge extends Model
{
    protected $table = 'invoice_item_surcharges';

    protected $fillable = [
        'invoice_item_id',
        'surcharge_id',
        'billing_code',
        'name',
        'amount_snapshot',
        'calculated_amount',
    ];

    protected $casts = [
        'amount_snapshot'   => 'decimal:2',
        'calculated_amount' => 'decimal:2',
    ];

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function surcharge(): BelongsTo
    {
        return $this->belongsTo(Surcharge::class, 'surcharge_id');
    }
}