<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * User repository interface
 * 
 * Defines data access methods for User model
 * 
 * @package App\Repositories\Contracts
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email address
     * 
     * @param string $email User email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Update user profile
     * 
     * @param string $id User ID (UUID)
     * @param array $data Profile data (name, email)
     * @return bool
     */
    public function updateProfile(string $id, array $data): bool;

    /**
     * Update user password
     * 
     * @param string $id User ID (UUID)
     * @param string $password New password (will be hashed)
     * @return bool
     */
    public function updatePassword(string $id, string $password): bool;

    /**
     * Enable 2FA for user
     * 
     * @param string $id User ID (UUID)
     * @return bool
     */
    public function enable2FA(string $id): bool;

    /**
     * Disable 2FA for user
     * 
     * @param string $id User ID (UUID)
     * @return bool
     */
    public function disable2FA(string $id): bool;
}
