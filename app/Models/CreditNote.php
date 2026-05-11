<?php
// FILE PATH: app/Models/CreditNote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    protected $table = 'credit_notes';

    protected $fillable = [
        'credit_note_number',
        'dispute_id',
        'invoice_id',
        'total_amount',
        'status',
        'xero_credit_note_id',
        'issued_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'issued_date'  => 'date',
    ];

    public const STATUSES = ['draft', 'authorised', 'paid'];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class, 'dispute_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public static function generateCreditNoteNumber(): string
    {
        $year = date('Y');
        $prefix = "CN-{$year}-";

        $last = self::where('credit_note_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $next = $last
            ? ((int) str_replace($prefix, '', $last->credit_note_number)) + 1
            : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}