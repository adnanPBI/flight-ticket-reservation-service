<?php

namespace Tests\Feature\Security;

use App\Services\Security\SensitiveDataMasker;
use Tests\TestCase;

class SensitiveDataMaskerTest extends TestCase
{
    public function test_sensitive_keys_are_masked_recursively(): void
    {
        $payload = [
            'email' => 'customer@example.com',
            'passport_number' => 'A1234567',
            'nested' => [
                'client_secret' => 'secret_123456789',
            ],
        ];

        $masked = app(SensitiveDataMasker::class)->maskArray($payload);

        $this->assertSame('c***@example.com', $masked['email']);
        $this->assertStringStartsWith('[masked:', $masked['passport_number']);
        $this->assertStringStartsWith('[masked:', $masked['nested']['client_secret']);
    }
}
