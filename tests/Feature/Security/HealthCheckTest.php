<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_secure_health_endpoint_returns_json(): void
    {
        $response = $this->getJson('/health/secure');

        $response->assertJsonStructure([
            'status',
            'checks' => ['app', 'database', 'cache', 'redis'],
            'timestamp',
        ]);
    }
}
