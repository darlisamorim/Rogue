<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'applicable_to',
        'max_uses',
        'current_uses',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'max_uses' => 'integer',
            'current_uses' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->max_uses !== null && $this->current_uses >= $this->max_uses) {
            return false;
        }

        $now = Carbon::now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $originalPrice): float
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return round($originalPrice * ($this->value / 100), 2);
        }

        return min((float) $this->value, $originalPrice);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
