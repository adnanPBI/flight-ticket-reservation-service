<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class AirlineSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['iata_code' => 'BG', 'icao_code' => 'BBC', 'name' => 'Biman Bangladesh Airlines', 'country' => 'Bangladesh'],
            ['iata_code' => 'EK', 'icao_code' => 'UAE', 'name' => 'Emirates', 'country' => 'United Arab Emirates'],
            ['iata_code' => 'QR', 'icao_code' => 'QTR', 'name' => 'Qatar Airways', 'country' => 'Qatar'],
            ['iata_code' => 'TK', 'icao_code' => 'THY', 'name' => 'Turkish Airlines', 'country' => 'Turkey'],
            ['iata_code' => 'BA', 'icao_code' => 'BAW', 'name' => 'British Airways', 'country' => 'United Kingdom'],
        ])->each(fn ($airline) => Airline::query()->updateOrCreate(['iata_code' => $airline['iata_code']], $airline));
    }
}
