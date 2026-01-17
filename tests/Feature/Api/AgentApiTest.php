<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Agent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Agent API feature test
 */
class AgentApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_register_new_agent(): void
    {
        $agentData = [
            'agent_id' => 'test-agent-001',
            'hwid' => 'hwid12345',
            'hostname' => 'test-server',
            'ip_address' => '192.168.1.100',
            'os_type' => 'Linux',
            'os_version' => 'Ubuntu 22.04',
            'cpu_cores' => 8,
            'total_memory' => 17179869184,
            'total_disk' => 1073741824000,
            'api_token' => 'test_api_token_123',
        ];

        $response = $this->postJson('/api/agent/register', $agentData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Agent registered successfully',
        ]);

        $this->assertDatabaseHas('agents', [
            'agent_id' => 'test-agent-001',
            'hwid' => 'hwid12345',
        ]);
    }

    #[Test]
    public function it_can_send_heartbeat(): void
    {
        $agent = Agent::factory()->create([
            'api_token' => 'valid_token',
            'status' => 'offline',
        ]);

        $response = $this->postJson('/api/agent/heartbeat', [], [
            'Authorization' => 'Bearer valid_token',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Heartbeat received',
        ]);

        $agent->refresh();
        $this->assertEquals('online', $agent->status);
    }

    #[Test]
    public function it_returns_error_when_heartbeat_without_token(): void
    {
        $response = $this->postJson('/api/agent/heartbeat');

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'API token required',
        ]);
    }

    #[Test]
    public function it_can_get_agents_list(): void
    {
        Agent::factory()->count(3)->create();

        $response = $this->getJson('/api/agents');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'summary' => ['total', 'online', 'offline'],
        ]);
    }

    #[Test]
    public function it_can_get_single_agent(): void
    {
        $agent = Agent::factory()->create();

        $response = $this->getJson("/api/agents/{$agent->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    #[Test]
    public function it_can_delete_agent(): void
    {
        $agent = Agent::factory()->create();

        $response = $this->deleteJson("/api/agents/{$agent->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertSoftDeleted('agents', ['id' => $agent->id]);
    }
}
