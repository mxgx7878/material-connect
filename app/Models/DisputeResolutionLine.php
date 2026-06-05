<?php
// FILE PATH: app/Models/DisputeResolutionLine.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeResolutionLine extends Model
{
    protected $table = 'dispute_resolution_lines';

    protected $fillable = [
        'dispute_id',
        'description',
        'quantity',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'amount'   => 'decimal:2',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class, 'dispute_id');
    }
}