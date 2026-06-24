<?php

namespace App\Models;

use App\Enums\FlightProvider;
use Illuminate\Database\Eloquent\Model;

class FlightProviderLog extends Model
{
    protected $fillable = [
        'provider', 'direction', 'endpoint', 'method', 'status_code', 'correlation_id', 'booking_id', 'flight_search_id',
        'request_payload', 'response_payload', 'error_message', 'duration_ms'
    ];
    protected function casts(): array { return ['provider' => FlightProvider::class, 'request_payload' => 'array', 'response_payload' => 'array']; }
}
