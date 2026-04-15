<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingConfig extends Model
{
    protected $fillable = [
        'action_slug',
        'label',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    const ACTION_FIRST_DOWNLOAD = 'first_download';
    const ACTION_REDOWNLOAD = 'redownload';
    const ACTION_TEMPLATE_CHANGE = 'template_change';

    public static function getPrice(string $actionSlug): float
    {
        $config = static::where('action_slug', $actionSlug)
            ->where('is_active', true)
            ->first();

        return $config ? (float) $config->price : 0.0;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
