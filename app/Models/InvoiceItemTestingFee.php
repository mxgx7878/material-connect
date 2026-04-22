<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItemTestingFee extends Model
{
    protected $table = 'invoice_item_testing_fees';

    protected $fillable = [
        'invoice_item_id',
        'testing_fee_id',
        'billing_code',
        'name',
        'amount_snapshot',
        'included',
    ];

    protected $casts = [
        'amount_snapshot' => 'decimal:2',
        'included'        => 'boolean',
    ];

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    public function testingFee(): BelongsTo
    {
        return $this->belongsTo(TestingFee::class, 'testing_fee_id');
    }
}