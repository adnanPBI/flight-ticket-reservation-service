<?php

namespace App\Data;

final readonly class PassengerData
{
    public function __construct(
        public string $type,
        public ?string $title,
        public string $firstName,
        public string $lastName,
        public string $dateOfBirth,
        public ?string $gender = null,
        public ?string $nationality = null,
        public ?string $passportNumber = null,
        public ?string $passportExpiryDate = null,
    ) {}
}
