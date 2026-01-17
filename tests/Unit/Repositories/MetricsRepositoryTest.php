<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\Agent;
use App\Models\Metric;
use App\Models\MetricSnapshot;
use App\Repositories\MetricsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MetricsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private MetricsRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MetricsRepository(new Metric());
    }

    #[Test]
    public function it_can_store_metrics_for_agent(): void
    {
        $agent = Agent::factory()->create();

        $metrics = [
            ['metric_type' => 'cpu', 'value' => 75.5, 'unit' => '%'],
            ['metric_type' => 'memory', 'value' => 65.3, 'unit' => '%'],
            ['metric_type' => 'disk', 'value' => 45.2, 'unit' => '%'],
        ];

        $result = $this->repository->storeMetrics($agent->id, $metrics);

        $this->assertTrue($result);
        $this->assertDatabaseCount('metrics', 3);
        $this->assertDatabaseHas('metrics', [
            'agent_id' => $agent->id,
            'metric_type' => 'cpu',
            'value' => 75.5,
        ]);
    }

    #[Test]
    public function it_can_get_recent_metrics(): void
    {
        $agent = Agent::factory()->create();

        // Create 10 metrics
        for ($i = 0; $i < 10; $i++) {
            Metric::create([
                'agent_id' => $agent->id,
                'metric_type' => 'cpu',
                'value' => 50 + $i,
                'unit' => '%',
                'recorded_at' => now()->subMinutes(10 - $i),
            ]);
        }

        $recent = $this->repository->getRecentMetrics($agent->id, 'cpu', 5);

        $this->assertCount(5, $recent);
        $this->assertEquals(59, $recent->first()->value); // Latest value
    }

    #[Test]
    public function it_can_get_latest_snapshot(): void
    {
        $agent = Agent::factory()->create();

        // Create metric snapshots
        MetricSnapshot::create([
            'agent_id' => $agent->id,
            'metric_type' => 'cpu',
            'avg_value' => 75.5,
            'min_value' => 60.0,
            'max_value' => 90.0,
            'low_value' => 65.0,
            'high_value' => 85.0,
            'snapshot_period' => '1min',
            'snapshot_time' => now(),
        ]);

        MetricSnapshot::create([
            'agent_id' => $agent->id,
            'metric_type' => 'memory',
            'avg_value' => 65.3,
            'min_value' => 50.0,
            'max_value' => 80.0,
            'low_value' => 55.0,
            'high_value' => 75.0,
            'snapshot_period' => '1min',
            'snapshot_time' => now(),
        ]);

        $snapshot = $this->repository->getLatestSnapshot($agent->id);

        $this->assertIsArray($snapshot);
        $this->assertArrayHasKey('cpu', $snapshot);
        $this->assertArrayHasKey('memory', $snapshot);
        $this->assertEquals(75.5, $snapshot['cpu']);
        $this->assertEquals(65.3, $snapshot['memory']);
    }

    #[Test]
    public function it_returns_null_for_non_existent_agent_snapshot(): void
    {
        $snapshot = $this->repository->getLatestSnapshot('non-existent-uuid');

        $this->assertNull($snapshot);
    }
}
