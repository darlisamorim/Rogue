<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resume extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'title',
        'data',
        'customization',
        'current_version',
        'is_downloaded',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'customization' => 'array',
            'current_version' => 'integer',
            'is_downloaded' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class)->orderByDesc('version_number');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
