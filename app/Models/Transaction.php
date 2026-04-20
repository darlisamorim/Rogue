<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Coupon;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'resume_id',
        'type',
        'amount',
        'gateway',
        'gateway_transaction_id',
        'payer_cpf',
        'coupon_id',
        'discount_amount',
        'gross_amount',
        'fee_amount',
        'net_amount',
        'status',
        'pix_qr_code',
        'pix_copy_paste',
        'expires_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'          => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'gross_amount'    => 'decimal:2',
            'fee_amount'      => 'decimal:2',
            'net_amount'      => 'decimal:2',
            'expires_at'      => 'datetime',
            'confirmed_at'    => 'datetime',
        ];
    }

    // Tipos de transação
    const TYPE_DOWNLOAD = 'download';
    const TYPE_REDOWNLOAD = 'redownload';
    const TYPE_TEMPLATE_CHANGE = 'template_change';
    const TYPE_CREDIT_PURCHASE = 'credit_purchase';

    // Status
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }
}
