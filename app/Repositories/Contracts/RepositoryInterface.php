<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Base repository interface
 * 
 * Defines common CRUD operations for all repositories
 * 
 * @package App\Repositories\Contracts
 */
interface RepositoryInterface
{
    /**
     * Get all records
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Find a record by ID
     * 
     * @param string $id Record ID (UUID)
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find(string $id);

    /**
     * Create a new record
     * 
     * @param array $data Record data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data);

    /**
     * Update a record
     * 
     * @param string $id Record ID (UUID)
     * @param array $data Updated data
     * @return bool
     */
    public function update(string $id, array $data): bool;

    /**
     * Delete a record
     * 
     * @param string $id Record ID (UUID)
     * @return bool
     */
    public function delete(string $id): bool;
}
