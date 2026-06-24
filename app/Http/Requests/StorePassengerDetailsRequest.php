<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePassengerDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Booking|null $booking */
        $booking = $this->route('booking');
        $passengerCount = $booking?->search
            ? $booking->search->adult_count + $booking->search->child_count + $booking->search->infant_count
            : 1;

        return [
            'customer_email' => ['required', 'email:rfc', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'passengers' => ['required', 'array', 'size:'.$passengerCount],
            'passengers.*.passenger_type' => ['required', Rule::in(['adult', 'child', 'infant_without_seat', 'infant'])],
            'passengers.*.title' => ['nullable', 'string', 'max:20'],
            'passengers.*.first_name' => ['required', 'string', 'max:120'],
            'passengers.*.last_name' => ['required', 'string', 'max:120'],
            'passengers.*.date_of_birth' => ['required', 'date', 'before:today'],
            'passengers.*.gender' => ['nullable', Rule::in(['male', 'female', 'other', 'unspecified'])],
            'passengers.*.nationality' => ['nullable', 'string', 'size:2'],
            'passengers.*.passport_number' => ['nullable', 'string', 'max:64'],
            'passengers.*.passport_expiry_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var Booking|null $booking */
            $booking = $this->route('booking');

            if (! $booking || ! $booking->search) {
                return;
            }

            $expected = [
                'adult' => (int) $booking->search->adult_count,
                'child' => (int) $booking->search->child_count,
                'infant_without_seat' => (int) $booking->search->infant_count,
            ];

            $actual = ['adult' => 0, 'child' => 0, 'infant_without_seat' => 0];

            foreach ((array) $this->input('passengers', []) as $passenger) {
                $type = $passenger['passenger_type'] ?? null;
                if ($type === 'infant') {
                    $type = 'infant_without_seat';
                }

                if (array_key_exists($type, $actual)) {
                    $actual[$type]++;
                }
            }

            foreach ($expected as $type => $count) {
                if ($actual[$type] !== $count) {
                    $validator->errors()->add('passengers', "Passenger type count mismatch for {$type}. Expected {$count}, received {$actual[$type]}.");
                }
            }

            if ($booking->offer_expires_at && $booking->offer_expires_at->isPast()) {
                $validator->errors()->add('booking', 'This selected offer has expired. Please search again.');
            }
        });
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        foreach ($validated['passengers'] ?? [] as &$passenger) {
            if (($passenger['passenger_type'] ?? null) === 'infant') {
                $passenger['passenger_type'] = 'infant_without_seat';
            }
            if (isset($passenger['nationality'])) {
                $passenger['nationality'] = strtoupper($passenger['nationality']);
            }
        }

        return $validated;
    }
}
