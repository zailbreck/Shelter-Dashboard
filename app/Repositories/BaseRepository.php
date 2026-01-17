<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Base repository implementation
 * 
 * Provides common CRUD operations for all repositories
 * 
 * @package App\Repositories
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @param Model $model Eloquent model instance
     */
    public function __construct(
        protected Model $model
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id)
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $id, array $data): bool
    {
        $record = $this->find($id);

        if (!$record) {
            return false;
        }

        return $record->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $id): bool
    {
        $record = $this->find($id);

        if (!$record) {
            return false;
        }

        return $record->delete();
    }
}
