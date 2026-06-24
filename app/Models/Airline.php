<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    protected $fillable = ['iata_code', 'icao_code', 'name', 'country', 'logo_url', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
