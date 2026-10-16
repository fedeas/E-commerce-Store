<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'customer_group',
        'b2b_requested_at',
        'b2b_approved_at',
        'b2b_rejected_at',
        'phone',
        'mobile',
        'company_name',
        'vat_number',
        'occupation',
        'company_address',
        'city',
        'postcode',
        'customer_notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'b2b_requested_at' => 'datetime',
            'b2b_approved_at' => 'datetime',
            'b2b_rejected_at' => 'datetime',
        ];
    }

    public function isB2b(): bool
    {
        return $this->customer_group === 'b2b';
    }

    public function hasPendingB2bRequest(): bool
    {
        return $this->b2b_requested_at !== null
            && $this->b2b_approved_at === null
            && $this->b2b_rejected_at === null
            && ! $this->isB2b();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
} 
