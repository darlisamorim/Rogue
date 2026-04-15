<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeVersion extends Model
{
    protected $fillable = [
        'resume_id',
        'version_number',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'version_number' => 'integer',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }
}
