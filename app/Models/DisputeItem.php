<?php
// FILE PATH: app/Models/DisputeItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeItem extends Model
{
    protected $table = 'dispute_items';

    protected $fillable = [
        'dispute_id',
        'invoice_item_id',
        'invoice_item_surcharge_id',
        'invoice_item_testing_fee_id',
        'disputed_quantity',
        'disputed_amount',
        'notes',
    ];

    protected $casts = [
        'disputed_quantity' => 'decimal:2',
        'disputed_amount'   => 'decimal:2',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class, 'dispute_id');
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function invoiceItemSurcharge(): BelongsTo
    {
        return $this->belongsTo(InvoiceItemSurcharge::class, 'invoice_item_surcharge_id');
    }

    public function invoiceItemTestingFee(): BelongsTo
    {
        return $this->belongsTo(InvoiceItemTestingFee::class, 'invoice_item_testing_fee_id');
    }
}