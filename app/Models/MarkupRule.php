<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkupRule extends Model
{
    protected $fillable = ['name', 'scope', 'match_rules', 'calculation_type', 'value', 'currency', 'priority', 'is_active', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'match_rules' => 'array',
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
            && (! $this->ends_at || $this->ends_at->gte(now()));
    }
}
