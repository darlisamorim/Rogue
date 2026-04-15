<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'thumbnail_url',
        'component_name',
        'config',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
