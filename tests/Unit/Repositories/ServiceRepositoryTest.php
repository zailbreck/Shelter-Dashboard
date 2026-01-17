<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Agent;
use App\Models\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ServiceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ServiceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ServiceRepository(new Service());
    }

    #[Test]
    public function it_can_get_services_by_agent(): void
    {
        $agent = Agent::factory()->create();

        Service::create([
            'agent_id' => $agent->id,
            'name' => 'nginx',
            'pid' => 1234,
            'cpu_percent' => 2.5,
            'memory_percent' => 5.3,
            'memory_mb' => 150,
            'user' => 'www-data',
            'recorded_at' => now(),
        ]);

        Service::create([
            'agent_id' => $agent->id,
            'name' => 'mysql',
            'pid' => 5678,
            'cpu_percent' => 15.2,
            'memory_percent' => 25.7,
            'memory_mb' => 800,
            'user' => 'mysql',
            'recorded_at' => now(),
        ]);

        $services = $this->repository->getByAgent($agent->id);

        $this->assertCount(2, $services);
    }

    #[Test]
    public function it_can_get_top_services_by_resource(): void
    {
        $agent = Agent::factory()->create();

        // Create services with different CPU usage
        for ($i = 1; $i <= 15; $i++) {
            Service::create([
                'agent_id' => $agent->id,
                'name' => "service-{$i}",
                'pid' => 1000 + $i,
                'cpu_percent' => $i * 5,
                'memory_percent' => 10.0,
                'memory_mb' => 100,
                'user' => 'root',
                'recorded_at' => now(),
            ]);
        }

        $topServices = $this->repository->getTopByResource($agent->id, 5);

        $this->assertCount(5, $topServices);
        // Check highest CPU is first
        $this->assertEquals(75.0, $topServices->first()->cpu_percent);
    }

    #[Test]
    public function it_can_bulk_upsert_services(): void
    {
        $agent = Agent::factory()->create();

        $servicesToAdd = [
            ['name' => 'apache2', 'pid' => 2345, 'cpu_percent' => 5.0],
            ['name' => 'redis', 'pid' => 3456, 'cpu_percent' => 3.5],
        ];

        $result = $this->repository->bulkUpsert($agent->id, $servicesToAdd);

        $this->assertTrue($result);
        $this->assertDatabaseHas('services', [
            'agent_id' => $agent->id,
            'name' => 'apache2',
        ]);
    }

    #[Test]
    public function it_can_delete_services_by_agent(): void
    {
        $agent = Agent::factory()->create();

        Service::create([
            'agent_id' => $agent->id,
            'name' => 'test-service',
            'pid' => 9999,
            'cpu_percent' => 1.0,
            'memory_percent' => 2.0,
            'memory_mb' => 50,
            'user' => 'root',
            'recorded_at' => now(),
        ]);

        $this->assertDatabaseCount('services', 1);

        $result = $this->repository->deleteByAgent($agent->id);

        $this->assertTrue($result);
        $this->assertDatabaseCount('services', 0);
    }
}
