<?php

namespace Ayup\LaravelLayerArchitecture\Repositories;

use Ayup\LaravelLayerArchitecture\Exceptions\NoModelRegisteredInRepositoryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class Repository implements RepositoryInterface
{
    /**
     * @inheritDoc
     * @throws NoModelRegisteredInRepositoryException
     */
    public function getModel(): Model
    {
        if(!property_exists($this, 'model')) {
            throw new NoModelRegisteredInRepositoryException(static::class);
        }

        return $this->model;
    }

    /**
     * Get a new instance of the model query builder.
     *
     * @return Builder
     * @throws NoModelRegisteredInRepositoryException
     */
    public function builder(): Builder
    {
        return $this->getModel()->newQuery();
    }

    /**
     * Create a new model instance.
     *
     * @param array $attributes
     * @return Model
     * @throws NoModelRegisteredInRepositoryException
     */
    public function create(array $attributes)
    {
        return $this->getModel()->newInstance($attributes);
    }

    /**
     * Update an existing model.
     *
     * @param Model|string|int $modelOrId
     * @param array $attributes
     * @return bool
     * @throws NoModelRegisteredInRepositoryException
     */
    public function update(Model|string|int $modelOrId, array $attributes)
    {
        $modelOrId = !$modelOrId instanceof Model
            ? $this->findOneUsingKey($modelOrId)
            : $modelOrId;

        return $modelOrId->update($attributes);
    }

    /**
     * Delete an existing model.
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model)
    {
        return $model->delete();
    }

    /**
     * Save changes to a model.
     *
     * @param Model $model
     * @return bool
     */
    public function save(Model $model): bool
    {
        return $model->save();
    }

    /**
     * Find a single model using the primary key.
     *
     * @param $key
     * @return Model|null
     * @throws NoModelRegisteredInRepositoryException
     */
    public function findOneUsingKey($key): ?Model
    {
        return $this->builder()->find($key)->sole();
    }

    /**
     * Find many records using a column and value.
     *
     * @param $value
     * @param string|null $column
     * @return mixed
     * @throws NoModelRegisteredInRepositoryException
     */
    public function findManyUsing($value, ?string $column = null): Collection
    {
        return $this->builder()->where($column, $value)->get();
    }
}
