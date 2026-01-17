<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Agent model factory
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agent>
 */
class AgentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Agent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agent_id' => 'agent-' . fake()->unique()->uuid(),
            'hwid' => fake()->unique()->regexify('[A-Z0-9]{16}'),
            'hostname' => fake()->domainWord() . '-server',
            'ip_address' => fake()->ipv4(),
            'api_token' => Str::random(64),
            'os_type' => fake()->randomElement(['Linux', 'Windows', 'macOS']),
            'os_version' => fake()->randomElement(['Ubuntu 22.04', 'Windows Server 2022', 'macOS 13']),
            'cpu_cores' => fake()->numberBetween(2, 32),
            'total_memory' => fake()->randomElement([
                4294967296,   // 4GB
                8589934592,   // 8GB
                17179869184,  // 16GB
                34359738368,  // 32GB
            ]),
            'total_disk' => fake()->randomElement([
                268435456000,  // 250GB
                536870912000,  // 500GB
                1073741824000, // 1TB
                2147483648000, // 2TB
            ]),
            'status' => fake()->randomElement(['online', 'offline']),
            'last_seen_at' => now(),
            'registered_at' => now(),
        ];
    }

    /**
     * Indicate that the agent is online.
     */
    public function online(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'online',
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Indicate that the agent is offline.
     */
    public function offline(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'offline',
            'last_seen_at' => now()->subHours(2),
        ]);
    }
}
