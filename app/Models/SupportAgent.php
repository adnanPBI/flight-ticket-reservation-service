<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportAgent extends Model
{
    protected $fillable = ['admin_user_id', 'display_name', 'is_online', 'last_seen_at'];
    protected function casts(): array { return ['is_online' => 'boolean', 'last_seen_at' => 'datetime']; }

    public function adminUser() { return $this->belongsTo(AdminUser::class); }
}
