<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Agent;
use App\Repositories\Contracts\MetricsRepositoryInterface;
use App\Services\MetricsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    private MetricsService $service;
    private $metricsRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metricsRepoMock = Mockery::mock(MetricsRepositoryInterface::class);
        $this->service = new MetricsService($this->metricsRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_store_metrics(): void
    {
        $agent = Agent::factory()->create();

        $metricsData = [
            ['metric_type' => 'cpu', 'value' => 65.5, 'unit' => '%'],
            ['metric_type' => 'memory', 'value' => 45.2, 'unit' => '%'],
        ];

        $this->metricsRepoMock
            ->shouldReceive('storeMetrics')
            ->once()
            ->with($agent->id, $metricsData)
            ->andReturn(true);

        $result = $this->service->storeMetrics($agent->id, $metricsData);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_can_get_realtime_metrics(): void
    {
        $agent = Agent::factory()->create();

        $snapshot = [
            'cpu' => 55.0,
            'memory' => 60.0,
            'disk' => 40.0,
        ];

        $this->metricsRepoMock
            ->shouldReceive('getLatestSnapshot')
            ->once()
            ->with($agent->id)
            ->andReturn($snapshot);

        $result = $this->service->getRealtimeMetrics($agent);

        $this->assertIsArray($result);
    }

    #[Test]
    public function it_can_get_historical_metrics(): void
    {
        $agent = Agent::factory()->create();

        // Use Eloquent Collection to match interface requirement
        $rawData = Collection::make([
            (object) ['value' => 50.0, 'timestamp' => now()->subHours(2)],
            (object) ['value' => 60.0, 'timestamp' => now()->subHours(1)],
            (object) ['value' => 70.0, 'timestamp' => now()],
        ]);

        $this->metricsRepoMock
            ->shouldReceive('getHourlyMetrics')
            ->times(4) // Called for cpu, memory, disk, network
            ->andReturn($rawData);

        $result = $this->service->getHistoricalMetrics($agent);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('cpu', $result);
    }
}
