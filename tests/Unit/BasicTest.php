<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    #[Test]
    public function it_verifies_repository_pattern_structure_exists(): void
    {
        // Test repository interfaces exist
        $this->assertTrue(interface_exists('App\Repositories\Contracts\RepositoryInterface'));
        $this->assertTrue(interface_exists('App\Repositories\Contracts\UserRepositoryInterface'));
        $this->assertTrue(interface_exists('App\Repositories\Contracts\AgentRepositoryInterface'));
        $this->assertTrue(interface_exists('App\Repositories\Contracts\MetricsRepositoryInterface'));
        $this->assertTrue(interface_exists('App\Repositories\Contracts\ServiceRepositoryInterface'));
    }

    #[Test]
    public function it_verifies_repository_implementations_exist(): void
    {
        $this->assertTrue(class_exists('App\Repositories\BaseRepository'));
        $this->assertTrue(class_exists('App\Repositories\UserRepository'));
        $this->assertTrue(class_exists('App\Repositories\AgentRepository'));
        $this->assertTrue(class_exists('App\Repositories\MetricsRepository'));
        $this->assertTrue(class_exists('App\Repositories\ServiceRepository'));
    }

    #[Test]
    public function it_verifies_service_classes_exist(): void
    {
        $this->assertTrue(class_exists('App\Services\AuthService'));
        $this->assertTrue(class_exists('App\Services\AgentService'));
        $this->assertTrue(class_exists('App\Services\MetricsService'));
    }

    #[Test]
    public function it_verifies_uuid_trait_exists(): void
    {
        $this->assertTrue(trait_exists('App\Traits\HasUuid'));
    }

    #[Test]
    public function it_verifies_models_use_uuid_trait(): void
    {
        $this->assertContains('App\Traits\HasUuid', class_uses('App\Models\User'));
        $this->assertContains('App\Traits\HasUuid', class_uses('App\Models\Agent'));
    }
}
