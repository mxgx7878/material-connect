<?php
// FILE PATH: app/Models/Invoice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'order_id',
        'client_id',
        'material_total',
        'delivery_total',
        'surcharges_total',
        'testing_total',
        'back_charges',
        'credits',
        'refunds',
        'gst_tax',
        'discount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'status',
        'issued_date',
        'due_date',
        'notes',
        'created_by',
        'xero_invoice_id',
        'paid_at',
    ];

    protected $casts = [
        'material_total'   => 'decimal:2',
        'delivery_total'   => 'decimal:2',
        'surcharges_total' => 'decimal:2',
        'testing_total'    => 'decimal:2',
        'back_charges'     => 'decimal:2',
        'credits'          => 'decimal:2',
        'refunds'          => 'decimal:2',
        'gst_tax'          => 'decimal:2',
        'discount'         => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'amount_paid'      => 'decimal:2',
        'balance_due'      => 'decimal:2',
        'issued_date'      => 'date',
        'due_date'         => 'date',
        'paid_at'          => 'datetime',
    ];

    public const STATUSES = [
        'Draft', 'Sent', 'Paid', 'Partially Paid', 'Overdue', 'Cancelled', 'Void'
    ];

    // ── Relations ──

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    // ── Helpers ──

    /**
     * Generate next invoice number: INV-2026-0001
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";

        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) str_replace($prefix, '', $lastInvoice->invoice_number);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }


    public function surcharges(): HasMany
    {
        return $this->hasMany(InvoiceItemSurcharge::class, 'invoice_item_id');
    }

    public function testingFees(): HasMany
    {
        return $this->hasMany(InvoiceItemTestingFee::class, 'invoice_item_id');
    }
}