<?php
// FILE PATH: app/Models/DisputeFeedback.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeFeedback extends Model
{
    protected $table = 'dispute_feedback';

    protected $fillable = [
        'dispute_id',
        'client_id',
        'rating',
        'comments',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class, 'dispute_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}