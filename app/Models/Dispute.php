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
        'type',
        'status',
        'reason',
        'requested_outcome',
        'resolution_outcome',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public const TYPES = [
        'whole_invoice', 'line_item', 'quantity', 'surcharge', 'testing_fee',
    ];

    public const STATUSES = [
        'open', 'under_review', 'resolved', 'rejected', 'withdrawn',
    ];

    public const OPEN_STATUSES = ['open', 'under_review'];

    public const RESOLUTION_OUTCOMES = [
        'full_refund', 'partial_refund', 'adjustment', 'rejected',
    ];

    // ── Relations ────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
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

    public function creditNote(): HasOne
    {
        return $this->hasOne(CreditNote::class, 'dispute_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Generate next dispute number: DSP-2026-0001
     * Mirrors Invoice::generateInvoiceNumber().
     */
    public static function generateDisputeNumber(): string
    {
        $year = date('Y');
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
}