<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Agent;
use App\Repositories\Contracts\AgentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Agent repository implementation
 * 
 * Handles database operations for Agent model
 * 
 * @package App\Repositories
 */
class AgentRepository extends BaseRepository implements AgentRepositoryInterface
{
    /**
     * @param Agent $model Agent model instance
     */
    public function __construct(Agent $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function findByApiToken(string $token): ?Agent
    {
        return $this->model->where('api_token', $token)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function withServiceCount(): Collection
    {
        return $this->model
            ->withCount(['services'])
            ->orderBy('status')
            ->orderBy('hostname')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getOnlineAgents(): Collection
    {
        return $this->model
            ->where('status', 'online')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getOfflineAgents(int $days = 5): Collection
    {
        return $this->model
            ->offlineForDays($days)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function withServices(string $id): ?Agent
    {
        return $this->model
            ->with([
                'services' => function ($q) {
                    $q->latest('recorded_at')->limit(10);
                }
            ])
            ->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function updateHeartbeat(string $id, string $status): bool
    {
        return $this->update($id, [
            'status' => $status,
            'last_seen' => now(),
        ]);
    }
}
