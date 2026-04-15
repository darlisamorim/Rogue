<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'provider',
        'credit_balance',
        'professional_title',
        'phone',
        'location',
        'linkedin_url',
        'website_url',
        'bio',
        'date_of_birth',
        'nationality',
        'cpf',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'credit_balance'    => 'decimal:2',
            'date_of_birth'     => 'date',
            'is_admin'          => 'boolean',
        ];
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }
}
