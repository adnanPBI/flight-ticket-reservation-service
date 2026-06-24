<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = ['code', 'description', 'discount_type', 'value', 'currency', 'max_discount_minor', 'usage_limit', 'used_count', 'is_active', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function isCurrentlyActive(): bool
    {
        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lte(now()))
            && (! $this->ends_at || $this->ends_at->gte(now()))
            && (! $this->usage_limit || $this->used_count < $this->usage_limit);
    }

    public function isUsableForCurrency(string $currency): bool
    {
        return ! $this->currency || strtoupper($this->currency) === strtoupper($currency);
    }
}
