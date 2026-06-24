<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReverbChatFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_start_chat_conversation(): void
    {
        $response = $this->postJson('/support/chat/start');

        $response->assertOk()
            ->assertJsonStructure([
                'conversation' => ['id', 'status', 'messages'],
                'visitor_token',
                'broadcast_channel',
            ]);
    }
}
