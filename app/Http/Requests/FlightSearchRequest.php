<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FlightSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'origin' => ['required', 'string', 'size:3'],
            'destination' => ['required', 'string', 'size:3', 'different:origin'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date', 'required_if:trip_type,round_trip'],
            'trip_type' => ['required', Rule::in(['one_way', 'round_trip'])],
            'adult_count' => ['required', 'integer', 'min:1', 'max:9'],
            'child_count' => ['nullable', 'integer', 'min:0', 'max:8'],
            'infant_count' => ['nullable', 'integer', 'min:0', 'max:4'],
            'cabin_class' => ['required', Rule::in(['economy', 'premium_economy', 'business', 'first'])],
            'currency' => ['nullable', 'string', 'size:3'],
            'provider' => ['nullable', Rule::in(['duffel', 'amadeus'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origin' => strtoupper((string) $this->input('origin')),
            'destination' => strtoupper((string) $this->input('destination')),
            'currency' => strtoupper((string) $this->input('currency', 'BDT')),
            'child_count' => (int) $this->input('child_count', 0),
            'infant_count' => (int) $this->input('infant_count', 0),
        ]);
    }
}
