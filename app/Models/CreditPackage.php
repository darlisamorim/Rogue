<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model
{
    protected $fillable = [
        'name',
        'price',
        'credits',
        'bonus_percentage',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'credits' => 'decimal:2',
            'bonus_percentage' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
