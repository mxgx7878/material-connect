<?php
// FILE PATH: app/Models/Dispute.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class Dispute extends Model
{
    protected $table = 'disputes';

    protected $fillable = [
        'dispute_number',
        'invoice_id',
        'client_id',
        'supplier_id',
        'type',
        'category',
        'status',
        'reason',
        'requested_outcome',
        'supplier_response_deadline',
        'supplier_responded_at',
        'supplier_response_notes',
        'supplier_proposed_outcome',
        'client_response',
        'client_response_notes',
        'client_responded_at',
        'escalated_at',
        'resolution_outcome',
        'resolution_amount',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'supplier_response_deadline' => 'datetime',
        'supplier_responded_at'      => 'datetime',
        'escalated_at'               => 'datetime',
        'resolved_at'                => 'datetime',
        'resolution_amount'          => 'decimal:2',
        'client_responded_at' => 'datetime',
    ];

    // ── Enums ────────────────────────────────────────────────────────

    public const TYPES = [
        'whole_invoice', 'line_item', 'quantity', 'surcharge', 'testing_fee',
    ];

    public const CATEGORIES = [
        'Faulty', 'Late', 'Missing', 'Quality',
    ];

    public const STATUSES = [
        'open',
        'awaiting_supplier_response',
        'supplier_responded',
        'under_review',
        'resolved',
        'rejected',
        'withdrawn',
    ];

    public const OPEN_STATUSES = [
        'open',
        'awaiting_supplier_response',
        'supplier_responded',
        'under_review',
    ];

    public const RESOLUTION_OUTCOMES = [
        'refund', 'replacement', 'partial_credit', 'rejection',
    ];

    public const SUPPLIER_RESPONSE_HOURS = 48;

    // ── Relations ────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DisputeItem::class, 'dispute_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DisputeAttachment::class, 'dispute_id');
    }

    public function resolutionLines(): HasMany
    {
        return $this->hasMany(DisputeResolutionLine::class, 'dispute_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(DisputeFeedback::class, 'dispute_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * DSP-2026-0001 format. Mirrors Invoice::generateInvoiceNumber().
     */
    public static function generateDisputeNumber(): string
    {
        $year   = date('Y');
        $prefix = "DSP-{$year}-";

        $last = self::where('dispute_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $next = $last
            ? ((int) str_replace($prefix, '', $last->dispute_number)) + 1
            : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['resolved', 'rejected', 'withdrawn'], true);
    }

    /**
     * Has the supplier blown past the 48-hour window without responding?
     */
    public function isSupplierWindowExpired(): bool
    {
        return $this->status === 'awaiting_supplier_response'
            && $this->supplier_response_deadline
            && now()->greaterThan($this->supplier_response_deadline);
    }
}