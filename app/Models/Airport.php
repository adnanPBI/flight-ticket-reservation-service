<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $fillable = ['iata_code', 'icao_code', 'name', 'city', 'country', 'timezone', 'latitude', 'longitude', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7']; }
}
