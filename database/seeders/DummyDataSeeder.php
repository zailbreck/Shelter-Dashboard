<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agent;
use App\Models\Metric;
use App\Models\MetricSnapshot;
use App\Models\Service;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Creating dummy agents...\n";

        // Create 5 agents with different statuses
        $agents = [
            [
                'hostname' => 'web-server-01',
                'ip_address' => '192.168.1.10',
                'os_type' => 'Linux',
                'os_version' => 'Ubuntu 22.04 LTS',
                'cpu_cores' => 4,
                'total_memory' => 8589934592, // 8GB
                'total_disk' => 214748364800, // 200GB
                'status' => 'online',
                'last_seen_at' => now(),
            ],
            [
                'hostname' => 'db-server-01',
                'ip_address' => '192.168.1.20',
                'os_type' => 'Linux',
                'os_version' => 'CentOS 8',
                'cpu_cores' => 8,
                'total_memory' => 17179869184, // 16GB
                'total_disk' => 536870912000, // 500GB
                'status' => 'online',
                'last_seen_at' => now()->subSeconds(30),
            ],
            [
                'hostname' => 'app-server-01',
                'ip_address' => '192.168.1.30',
                'os_type' => 'Windows',
                'os_version' => 'Windows Server 2022',
                'cpu_cores' => 6,
                'total_memory' => 12884901888, // 12GB
                'total_disk' => 322122547200, // 300GB
                'status' => 'online',
                'last_seen_at' => now()->subMinute(),
            ],
            [
                'hostname' => 'cache-server-01',
                'ip_address' => '192.168.1.40',
                'os_type' => 'Linux',
                'os_version' => 'Debian 11',
                'cpu_cores' => 2,
                'total_memory' => 4294967296, // 4GB
                'total_disk' => 107374182400, // 100GB
                'status' => 'online',
                'last_seen_at' => now()->subSeconds(45),
            ],
            [
                'hostname' => 'backup-server-01',
                'ip_address' => '192.168.1.50',
                'os_type' => 'Linux',
                'os_version' => 'Ubuntu 20.04 LTS',
                'cpu_cores' => 4,
                'total_memory' => 8589934592, // 8GB
                'total_disk' => 1099511627776, // 1TB
                'status' => 'offline',
                'last_seen_at' => now()->subHours(2),
            ],
        ];

        foreach ($agents as $agentData) {
            $agent = Agent::create($agentData);
            echo "  Created agent: {$agent->hostname}\n";

            // Generate metrics for the last 7 days
            $this->generateMetrics($agent);

            // Generate metric snapshots
            $this->generateMetricSnapshots($agent);

            // Generate services
            $this->generateServices($agent);
        }

        echo "\n✅ Dummy data generation complete!\n";
        echo "   Total Agents: " . Agent::count() . "\n";
        echo "   Total Metrics: " . Metric::count() . "\n";
        echo "   Total Snapshots: " . MetricSnapshot::count() . "\n";
        echo "   Total Services: " . Service::count() . "\n";
    }

    private function generateMetrics(Agent $agent)
    {
        echo "    Generating metrics for {$agent->hostname}...\n";

        $days = 7;
        $interval = 300; // 5 minutes in seconds
        $startDate = now()->subDays($days);

        $metricTypes = [
            'cpu' => ['unit' => '%', 'base' => 40, 'variance' => 30],
            'memory' => ['unit' => '%', 'base' => 60, 'variance' => 20],
            'disk' => ['unit' => '%', 'base' => 50, 'variance' => 10],
            'network' => ['unit' => 'Mbps', 'base' => 100, 'variance' => 80],
            'io' => ['unit' => 'MB/s', 'base' => 50, 'variance' => 40],
        ];

        $metrics = [];
        $currentTime = $startDate->copy();
        $endTime = now();

        $batchSize = 1000;
        $count = 0;

        while ($currentTime->lessThan($endTime)) {
            foreach ($metricTypes as $type => $config) {
                // Create realistic patterns with daily cycles
                $hourOfDay = (int) $currentTime->format('H');
                $dayModifier = $this->getDailyModifier($hourOfDay);

                // Random spike occasionally
                $spike = (rand(1, 100) > 95) ? rand(20, 40) : 0;

                $value = $config['base'] +
                    ($config['variance'] * $dayModifier) +
                    $spike +
                    (rand(-10, 10)); // random noise

                $value = max(0, min(100, $value)); // Clamp between 0-100 for percentages

                if ($config['unit'] !== '%') {
                    $value = max(0, $value * 2); // Scale up for non-percentage metrics
                }

                $metrics[] = [
                    'agent_id' => $agent->id,
                    'metric_type' => $type,
                    'value' => round($value, 2),
                    'unit' => $config['unit'],
                    'recorded_at' => $currentTime->toDateTimeString(),
                    'created_at' => $currentTime->toDateTimeString(),
                ];

                $count++;

                // Insert in batches for performance
                if (count($metrics) >= $batchSize) {
                    Metric::insert($metrics);
                    $metrics = [];
                }
            }

            $currentTime->addSeconds($interval);
        }

        // Insert remaining metrics
        if (!empty($metrics)) {
            Metric::insert($metrics);
        }

        echo "      Generated {$count} metrics\n";
    }

    private function generateMetricSnapshots(Agent $agent)
    {
        echo "    Generating metric snapshots for {$agent->hostname}...\n";

        $metricTypes = ['cpu', 'memory', 'disk', 'network', 'io'];
        $periods = ['1min', '5min', '1hour', '1day'];

        $snapshots = [];
        $count = 0;

        foreach ($metricTypes as $type) {
            foreach ($periods as $period) {
                // Generate snapshots for the last 24 hours
                $startTime = now()->subHours(24);
                $currentTime = $startTime->copy();

                $intervalMinutes = match ($period) {
                    '1min' => 1,
                    '5min' => 5,
                    '1hour' => 60,
                    '1day' => 1440,
                };

                while ($currentTime->lessThan(now())) {
                    // Get actual metrics for this time period
                    $periodMetrics = Metric::where('agent_id', $agent->id)
                        ->where('metric_type', $type)
                        ->whereBetween('recorded_at', [
                            $currentTime->copy(),
                            $currentTime->copy()->addMinutes($intervalMinutes)
                        ])
                        ->pluck('value');

                    if ($periodMetrics->count() > 0) {
                        $sorted = $periodMetrics->sort()->values();

                        $snapshots[] = [
                            'agent_id' => $agent->id,
                            'metric_type' => $type,
                            'avg_value' => round($periodMetrics->avg(), 2),
                            'min_value' => round($periodMetrics->min(), 2),
                            'max_value' => round($periodMetrics->max(), 2),
                            'low_value' => round($sorted[intval($sorted->count() * 0.25)] ?? $sorted->first(), 2),
                            'high_value' => round($sorted[intval($sorted->count() * 0.75)] ?? $sorted->last(), 2),
                            'snapshot_period' => $period,
                            'snapshot_time' => $currentTime->toDateTimeString(),
                            'created_at' => now()->toDateTimeString(),
                        ];

                        $count++;
                    }

                    $currentTime->addMinutes($intervalMinutes);
                }
            }
        }

        if (!empty($snapshots)) {
            MetricSnapshot::insert($snapshots);
        }

        echo "      Generated {$count} snapshots\n";
    }

    private function generateServices(Agent $agent)
    {
        echo "    Generating services for {$agent->hostname}...\n";

        $isLinux = $agent->os_type === 'Linux';

        $serviceTemplates = $isLinux ? [
            ['name' => 'nginx', 'user' => 'www-data', 'cpu' => 5, 'mem' => 200],
            ['name' => 'mysql', 'user' => 'mysql', 'cpu' => 15, 'mem' => 1024],
            ['name' => 'php-fpm', 'user' => 'www-data', 'cpu' => 10, 'mem' => 512],
            ['name' => 'redis', 'user' => 'redis', 'cpu' => 3, 'mem' => 100],
            ['name' => 'cron', 'user' => 'root', 'cpu' => 0.5, 'mem' => 10],
            ['name' => 'sshd', 'user' => 'root', 'cpu' => 0.2, 'mem' => 5],
            ['name' => 'systemd', 'user' => 'root', 'cpu' => 1, 'mem' => 50],
            ['name' => 'docker', 'user' => 'root', 'cpu' => 8, 'mem' => 800],
        ] : [
            ['name' => 'IIS', 'user' => 'SYSTEM', 'cpu' => 10, 'mem' => 500],
            ['name' => 'SQL Server', 'user' => 'SYSTEM', 'cpu' => 20, 'mem' => 2048],
            ['name' => 'Windows Defender', 'user' => 'SYSTEM', 'cpu' => 5, 'mem' => 200],
            ['name' => 'Task Scheduler', 'user' => 'SYSTEM', 'cpu' => 0.5, 'mem' => 15],
            ['name' => 'Windows Update', 'user' => 'SYSTEM', 'cpu' => 2, 'mem' => 100],
        ];

        $services = [];
        foreach ($serviceTemplates as $index => $template) {
            $services[] = [
                'agent_id' => $agent->id,
                'name' => $template['name'],
                'pid' => rand(100, 65000),
                'status' => (rand(1, 100) > 10) ? 'running' : 'stopped', // 90% running
                'cpu_percent' => round($template['cpu'] + rand(-2, 5), 2),
                'memory_percent' => round(($template['mem'] / ($agent->total_memory / 1024 / 1024)) * 100, 2),
                'memory_mb' => $template['mem'] + rand(-50, 50),
                'disk_read_mb' => round(rand(0, 100) / 10, 2),
                'disk_write_mb' => round(rand(0, 50) / 10, 2),
                'user' => $template['user'],
                'command' => $isLinux ? "/usr/bin/{$template['name']}" : "C:\\Windows\\System32\\{$template['name']}.exe",
                'recorded_at' => now()->toDateTimeString(),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        Service::insert($services);
        echo "      Generated " . count($services) . " services\n";
    }

    /**
     * Get a modifier based on hour of day for realistic daily patterns
     * Returns value between -1 and 1
     */
    private function getDailyModifier(int $hour): float
    {
        // Model daily usage pattern: low at night, high during work hours
        if ($hour >= 0 && $hour < 6) {
            return -0.6; // Very low usage at night
        } elseif ($hour >= 6 && $hour < 9) {
            return 0; // Morning ramp up
        } elseif ($hour >= 9 && $hour < 17) {
            return 0.6; // High usage during work hours
        } elseif ($hour >= 17 && $hour < 22) {
            return 0.2; // Evening usage
        } else {
            return -0.4; // Late evening
        }
    }
}
