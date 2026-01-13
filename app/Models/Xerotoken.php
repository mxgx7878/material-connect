<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroToken extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'tenant_id',
        'tenant_name',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function hasExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function expiresSoon(): bool
    {
        return $this->expires_at->subMinutes(5)->isPast();
    }
}