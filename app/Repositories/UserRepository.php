<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

/**
 * User repository implementation
 * 
 * Handles database operations for User model
 * 
 * @package App\Repositories
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @param User $model User model instance
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function updateProfile(string $id, array $data): bool
    {
        return $this->update($id, [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function updatePassword(string $id, string $password): bool
    {
        return $this->update($id, [
            'password' => $password,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function enable2FA(string $id): bool
    {
        return $this->update($id, [
            'google2fa_enabled' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function disable2FA(string $id): bool
    {
        return $this->update($id, [
            'google2fa_enabled' => false,
            'google2fa_secret' => null,
        ]);
    }
}
