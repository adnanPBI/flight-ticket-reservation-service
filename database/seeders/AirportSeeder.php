<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['iata_code' => 'DAC', 'icao_code' => 'VGHS', 'name' => 'Hazrat Shahjalal International Airport', 'city' => 'Dhaka', 'country' => 'Bangladesh', 'timezone' => 'Asia/Dhaka'],
            ['iata_code' => 'CXB', 'icao_code' => 'VGCB', 'name' => "Cox's Bazar Airport", 'city' => "Cox's Bazar", 'country' => 'Bangladesh', 'timezone' => 'Asia/Dhaka'],
            ['iata_code' => 'CGP', 'icao_code' => 'VGEG', 'name' => 'Shah Amanat International Airport', 'city' => 'Chattogram', 'country' => 'Bangladesh', 'timezone' => 'Asia/Dhaka'],
            ['iata_code' => 'JFK', 'icao_code' => 'KJFK', 'name' => 'John F. Kennedy International Airport', 'city' => 'New York', 'country' => 'United States', 'timezone' => 'America/New_York'],
            ['iata_code' => 'LHR', 'icao_code' => 'EGLL', 'name' => 'Heathrow Airport', 'city' => 'London', 'country' => 'United Kingdom', 'timezone' => 'Europe/London'],
            ['iata_code' => 'DXB', 'icao_code' => 'OMDB', 'name' => 'Dubai International Airport', 'city' => 'Dubai', 'country' => 'United Arab Emirates', 'timezone' => 'Asia/Dubai'],
        ])->each(fn ($airport) => Airport::query()->updateOrCreate(['iata_code' => $airport['iata_code']], $airport));
    }
}
