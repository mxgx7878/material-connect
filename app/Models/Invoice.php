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
        'subtotal',
        'delivery_total',
        'gst_tax',
        'discount',
        'total_amount',
        'status',
        'issued_date',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal'       => 'decimal:2',
        'delivery_total' => 'decimal:2',
        'gst_tax'        => 'decimal:2',
        'discount'       => 'decimal:2',
        'total_amount'   => 'decimal:2',
        'issued_date'    => 'date',
        'due_date'       => 'date',
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
}