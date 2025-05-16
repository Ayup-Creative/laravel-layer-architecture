<?php

namespace Ayup\LaravelLayerArchitecture\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * Get the model from the repository.
     */
    public function getModel(): Model;

    /**
     * Get a new instance of the model query builder.
     */
    public function builder(): Builder;

    /**
     * Create a new model instance.
     *
     * @return Model
     */
    public function create(array $attributes);

    /**
     * Update an existing model.
     *
     * @return bool
     */
    public function update(Model|string|int $modelOrId, array $attributes);

    /**
     * Delete an existing model.
     *
     * @return bool
     */
    public function delete(Model $model);

    /**
     * Save changes to a model.
     */
    public function save(Model $model): bool;

    /**
     * Find a single model using the primary key.
     */
    public function findOneUsingKey($key): ?Model;

    /**
     * Find many records using a column and value.
     *
     * @return mixed
     */
    public function findManyUsing($value, ?string $column = null): Collection;
}
