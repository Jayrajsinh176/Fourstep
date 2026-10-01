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

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_owner',
        'status',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    /**
     * Allow access to Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->status == 1;
    }

    /**
     * Check if the user is the owner.
     */
    public function isOwner(): bool
    {
        return (bool) $this->is_owner;
    }

    /**
     * Check a permission using dot notation.
     *
     * Example:
     * hasPermission('products.view')
     * hasPermission('dashboard')
     */
    public function hasPermission(string $key): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return (bool) data_get($this->permissions ?? [], $key, false);
    }
}