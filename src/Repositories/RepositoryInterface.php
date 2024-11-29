<?php

namespace Ayup\LaravelLayerArchitecture\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * Get the model from the repository.
     *
     * @return Model
     */
    public function getModel(): Model;

    /**
     * Get a new instance of the model query builder.
     *
     * @return Builder
     */
    public function builder(): Builder;

    /**
     * Create a new model instance.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes);

    /**
     * Update an existing model.
     *
     * @param Model|string|int $modelOrId
     * @param array $attributes
     * @return bool
     */
    public function update(Model|string|int $modelOrId, array $attributes);

    /**
     * Delete an existing model.
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model);

    /**
     * Save changes to a model.
     *
     * @param Model $model
     * @return bool
     */
    public function save(Model $model): bool;

    /**
     * Find a single model using the primary key.
     *
     * @param $key
     * @return Model|null
     */
    public function findOneUsingKey($key): ?Model;

    /**
     * Find many records using a column and value.
     *
     * @param $value
     * @param string|null $column
     * @return mixed
     */
    public function findManyUsing($value, ?string $column = null): Collection;
}
