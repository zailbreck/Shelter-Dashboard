<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * UUID trait for models
 * 
 * Automatically generates UUID for primary key
 * 
 * @package App\Traits
 */
trait HasUuid
{
    /**
     * Boot the trait
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the value indicating whether the IDs are incrementing
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Get the auto-incrementing key type
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
