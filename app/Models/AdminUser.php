<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdminUser extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'last_login_at'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_active' => 'boolean', 'last_login_at' => 'datetime'];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active === true && in_array($this->role, ['super_admin', 'admin', 'support', 'finance', 'operations'], true);
    }

    public function supportAgent()
    {
        return $this->hasOne(SupportAgent::class);
    }
}
