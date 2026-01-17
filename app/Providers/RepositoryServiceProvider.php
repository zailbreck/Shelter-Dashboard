<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\AgentRepository;
use App\Repositories\Contracts\AgentRepositoryInterface;
use App\Repositories\Contracts\MetricsRepositoryInterface;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\MetricsRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Repository service provider
 * 
 * Binds repository interfaces to their implementations
 * 
 * @package App\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            AgentRepositoryInterface::class,
            AgentRepository::class
        );

        $this->app->bind(
            MetricsRepositoryInterface::class,
            MetricsRepository::class
        );

        $this->app->bind(
            ServiceRepositoryInterface::class,
            ServiceRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
